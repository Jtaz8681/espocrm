<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

namespace Espo\EntryPoints;

use Espo\Repositories\Attachment as AttachmentRepository;

use Espo\Core\Acl;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\FileStorage\Manager as FileStorageManager;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment;

use GdImage;
use Throwable;

class Image implements EntryPoint
{
    /** @var ?string[] */
    protected $allowedRelatedTypeList = null;
    /** @var ?string[] */
    protected $allowedFieldList = null;

    private const string TYPE_SVG = 'image/svg+xml';

    /**
     * @var string[]
     */
    private array $noInlineTypeList = [
        self::TYPE_SVG,
    ];

    public function __construct(
        private FileStorageManager $fileStorageManager,
        private FileManager $fileManager,
        protected Acl $acl,
        protected EntityManager $entityManager,
        protected Config $config,
        protected Metadata $metadata
    ) {}

    public function run(Request $request, Response $response): void
    {
        $id = $request->getQueryParam('id');
        $size = $request->getQueryParam('size') ?? null;

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $this->show($request, $response, $id, $size);
    }

    /**
     * @throws Error
     * @throws NotFoundSilent
     * @throws NotFound
     * @throws ForbiddenSilent
     */
    protected function show(
        Request $request,
        Response $response,
        string $id,
        ?string $size,
        bool $disableAccessCheck = false,
        bool $noCacheHeaders = false,
    ): void {

        $attachment = $this->entityManager->getRDBRepositoryByClass(Attachment::class)->getById($id);

        if (!$attachment) {
            throw new NotFoundSilent("Attachment not found.");
        }

        if (!$disableAccessCheck && !$this->acl->checkEntity($attachment)) {
            throw new ForbiddenSilent("No access to attachment.");
        }

        $fileType = $attachment->getType();

        if (!in_array($fileType, $this->getAllowedFileTypeList())) {
            throw new ForbiddenSilent("Not allowed file type '$fileType'.");
        }

        if ($this->allowedRelatedTypeList) {
            if (!in_array($attachment->getRelatedType(), $this->allowedRelatedTypeList)) {
                throw new NotFoundSilent("Not allowed related type.");
            }
        }

        if ($this->allowedFieldList) {
            if (!in_array($attachment->getTargetField(), $this->allowedFieldList)) {
                throw new NotFoundSilent("Not allowed field.");
            }
        }

        $fileSize = 0;
        $fileName = $attachment->getName();

        $toResize = $size && in_array($fileType, $this->getResizableFileTypeList());

        if ($toResize) {
            $contents = $this->getThumbContents($attachment, $size);

            if ($contents) {
                $fileName = $size . '-' . $attachment->getName();
                $fileSize = strlen($contents);

                $response->writeBody($contents);
            } else {
                $toResize = false;
            }
        }

        if (!$toResize) {
            $stream = $this->fileStorageManager->getStream($attachment);
            $fileSize = $stream->getSize() ?? $this->fileStorageManager->getSize($attachment);

            $response->setBody($stream);
        }

        if ($fileType) {
            $response->setHeader('Content-Type', $fileType);
        }

        $fileName = self::sanitizeFileName($fileName);

        $csp =
            "default-src 'none'; script-src 'none'; object-src 'none'; form-action 'none'; " .
            "style-src 'unsafe-inline'; sandbox;";

        $disposition = $this->chooseDisposition($fileType, $request);

        $response
            ->setHeader('Content-Disposition', "$disposition;filename=\"$fileName\"")
            ->setHeader('Content-Length', (string) $fileSize)
            ->setHeader('Content-Security-Policy', $csp);

        if (!$noCacheHeaders) {
            $response->setHeader('Cache-Control', 'private, max-age=864000, immutable');
        }
    }

    /**
     * @throws Error
     * @throws NotFound
     */
    private function getThumbContents(Attachment $attachment, string $size): ?string
    {
        if (!array_key_exists($size, $this->getSizes())) {
            throw new Error("Bad size.");
        }

        $useCache = !$this->config->get('thumbImageCacheDisabled', false);

        $sourceId = $attachment->getSourceId();

        $file = basename("{$sourceId}_$size");

        $cacheFilePath = "data/upload/thumbs/$file";

        if ($useCache && $this->fileManager->isFile($cacheFilePath)) {
            return $this->fileManager->getContents($cacheFilePath);
        }

        $filePath = $this->getAttachmentRepository()->getFilePath($attachment);

        if (!$this->fileManager->isFile($filePath)) {
            throw new NotFound("File not found.");
        }

        $fileType = $attachment->getType() ?? '';

        $targetImage = $this->createThumbImage($filePath, $fileType, $size);

        if (!$targetImage) {
            return null;
        }

        ob_start();

        switch ($fileType) {
            case 'image/jpeg':
                imagejpeg($targetImage);

                break;

            case 'image/png':
                imagepng($targetImage);

                break;

            case 'image/gif':
                imagegif($targetImage);

                break;

            case 'image/webp':
                imagewebp($targetImage);

                break;
        }

        $contents = ob_get_contents() ?: '';

        ob_end_clean();

        imagedestroy($targetImage);

        if ($useCache) {
            $this->fileManager->putContents($cacheFilePath, $contents);
        }

        return $contents;
    }

    /**
     * @throws Error
     */
    private function createThumbImage(string $filePath, string $fileType, string $size): ?GdImage
    {
        if (!is_array(getimagesize($filePath))) {
            throw new Error();
        }

        [$originalWidth, $originalHeight] = getimagesize($filePath);

        [$width, $height] = $this->getSizes()[$size];

        if ($originalWidth <= $width && $originalHeight <= $height) {
            $targetWidth = $originalWidth;
            $targetHeight = $originalHeight;
        } else {
            if ($originalWidth > $originalHeight) {
                $targetWidth = $width;
                $targetHeight = (int) ($originalHeight / ($originalWidth / $width));

                if ($targetHeight > $height) {
                    $targetHeight = $height;
                    $targetWidth = (int) ($originalWidth / ($originalHeight / $height));
                }
            } else {
                $targetHeight = $height;
                $targetWidth = (int) ($originalWidth / ($originalHeight / $height));

                if ($targetWidth > $width) {
                    $targetWidth = $width;
                    $targetHeight = (int) ($originalHeight / ($originalWidth / $width));
                }
            }
        }

        if ($targetWidth < 1) {
            $targetWidth = 1;
        }

        if ($targetHeight < 1) {
            $targetHeight = 1;
        }

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($targetImage === false) {
            return null;
        }

        switch ($fileType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filePath);

                if ($sourceImage === false) {
                    return null;
                }

                $this->resample(
                    $targetImage,
                    $sourceImage,
                    $targetWidth,
                    $targetHeight,
                    $originalWidth,
                    $originalHeight
                );

                break;

            case 'image/png':
                $sourceImage = imagecreatefrompng($filePath);

                if ($sourceImage === false) {
                    return null;
                }

                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);

                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);

                if ($transparent !== false) {
                    imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);
                }

                $this->resample(
                    $targetImage,
                    $sourceImage,
                    $targetWidth,
                    $targetHeight,
                    $originalWidth,
                    $originalHeight
                );

                break;

            case 'image/gif':
                $sourceImage = imagecreatefromgif($filePath);

                if ($sourceImage === false) {
                    return null;
                }

                $this->resample(
                    $targetImage,
                    $sourceImage,
                    $targetWidth,
                    $targetHeight,
                    $originalWidth,
                    $originalHeight
                );

                break;

            case 'image/webp':
                try {
                    $sourceImage = imagecreatefromwebp($filePath);
                } catch (Throwable) {
                    return null;
                }

                if ($sourceImage === false) {
                    return null;
                }

                $this->resample(
                    $targetImage,
                    $sourceImage,
                    $targetWidth,
                    $targetHeight,
                    $originalWidth,
                    $originalHeight
                );

                break;
        }

        if (in_array($fileType, $this->getFixOrientationFileTypeList())) {
            $targetImage = $this->fixOrientation($targetImage, $filePath);
        }

        return $targetImage;
    }

    /**
     * @param string $filePath
     * @return ?int
     */
    private function getOrientation(string $filePath)
    {
        if (!function_exists('exif_read_data')) {
            return 0;
        }

        $data = exif_read_data($filePath) ?: [];

        return $data['Orientation'] ?? null;
    }

    private function fixOrientation(GdImage $targetImage, string $filePath): GdImage
    {
        $orientation = $this->getOrientation($filePath);

        if ($orientation) {
            $angle = [0, 0, 0, 180, 0, 0, -90, 0, 90][$orientation] ?? 0;

            $targetImage = imagerotate($targetImage, $angle, 0) ?: $targetImage;
        }

        return $targetImage;
    }

    /**
     * @return string[]
     */
    private function getAllowedFileTypeList(): array
    {
        return $this->metadata->get(['app', 'image', 'allowedFileTypeList']) ?? [];
    }

    /**
     * @return string[]
     */
    private function getResizableFileTypeList(): array
    {
        return $this->metadata->get(['app', 'image', 'resizableFileTypeList']) ?? [];
    }

    /**
     * @return string[]
     */
    private function getFixOrientationFileTypeList(): array
    {
        return $this->metadata->get(['app', 'image', 'fixOrientationFileTypeList']) ?? [];
    }

    /**
     * @return array<string, array{int, int}>
     */
    protected function getSizes(): array
    {
        return $this->metadata->get(['app', 'image', 'sizes']) ?? [];
    }

    private function getAttachmentRepository(): AttachmentRepository
    {
        /** @var AttachmentRepository */
        return $this->entityManager->getRepository(Attachment::ENTITY_TYPE);
    }

    private function resample(
        GdImage $targetImage,
        GdImage $sourceImage,
        int $targetWidth,
        int $targetHeight,
        int $originalWidth,
        int $originalHeight
    ): void {

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0, 0, 0,
            $targetWidth, $targetHeight, $originalWidth, $originalHeight
        );
    }

    private function chooseDisposition(?string $fileType, Request $request): string
    {
        $disposition = 'inline';

        if (
            in_array($fileType, $this->noInlineTypeList) &&
            in_array($request->getHeader('Sec-Fetch-Dest'), [
                'document',
                'iframe',
            ])
        ) {
            $disposition = 'attachment';
        }

        return $disposition;
    }

    private static function sanitizeFileName(?string $fileName): string
    {
        return str_replace("\"", "\\\"", $fileName ?? '');
    }
}

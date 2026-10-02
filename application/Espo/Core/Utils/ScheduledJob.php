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

namespace Espo\Core\Utils;

use Espo\Core\Job\MetadataProvider;

use Espo\Entities\Job;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\DateTime as DateTimeUtil;

use Espo\ORM\Name\Attribute;
use Exception;
use RuntimeException;
use DateTime;

class ScheduledJob
{
    protected string $cronFile = 'cron.php';
    /**
     * Period to check if crontab is configured properly.
     */
    protected string $checkingCronPeriod = '25 hours';
    /** @var array<string, string> */
    protected $cronSetup = [
        'linux' => '* * * * * cd {DOCUMENT_ROOT}; {PHP-BINARY} -f {CRON-FILE} > /dev/null 2>&1',
        'windows' => '{PHP-BINARY} -f {FULL-CRON-PATH}',
        'mac' => '* * * * * cd {DOCUMENT_ROOT}; {PHP-BINARY} -f {CRON-FILE} > /dev/null 2>&1',
        'default' => '* * * * * cd {DOCUMENT_ROOT}; {PHP-BINARY} -f {CRON-FILE} > /dev/null 2>&1',
    ];

    private System $systemUtil;

    public function __construct(
        private ClassFinder $classFinder,
        private EntityManager $entityManager,
        private Language $language,
        private MetadataProvider $metadataProvider
    ) {
        $this->systemUtil = new System();
    }

    /**
     * @return string[]
     */
    public function getAvailableList(): array
    {
        $list = array_filter(
            array_merge(
                $this->metadataProvider->getNonSystemScheduledJobNameList(),
                array_keys(
                    $this->classFinder->getMap('Jobs')
                )
            ),
            function (string $item) {
                return !$this->metadataProvider->isJobSystem($item);
            }
        );

        asort($list);

        return array_values($list);
    }

    /**
     * @return array{
     *   message: string,
     *   command: string,
     * }
     */
    public function getSetupMessage(): array
    {
        $language = $this->language;

        $OS = $this->systemUtil->getOS();

        $desc = $language->translate('cronSetup', 'options', 'ScheduledJob');

        if (!is_array($desc)) {
            $desc = [];
        }

        $data = [
            'PHP-BINARY' => $this->systemUtil->getPhpBinary(),
            'CRON-FILE' => $this->cronFile,
            'DOCUMENT_ROOT' => $this->systemUtil->getRootDir(),
            'FULL-CRON-PATH' => Util::concatPath($this->systemUtil->getRootDir(), $this->cronFile),
        ];

        $message = $desc[$OS] ?? $desc['default'];

        $command = $this->cronSetup[$OS] ?? $this->cronSetup['default'];

        foreach ($data as $name => $value) {
            /** @var string $command */
            $command = str_replace('{' . $name . '}', $value ?? '', $command);
        }

        return [
            'message' => $message,
            'command' => $command,
        ];
    }

    /**
     * Check if crontab is configured properly.
     */
    public function isCronConfigured(): bool
    {
        try {
            $r1From = new DateTime('-' . $this->checkingCronPeriod);
            $r1To = new DateTime('+' . $this->checkingCronPeriod);
        } catch (Exception) {
            throw new RuntimeException();
        }

        $r2From = new DateTime('-1 hour');
        $r2To = new DateTime();

        $format = DateTimeUtil::SYSTEM_DATE_TIME_FORMAT;

        return (bool) $this->entityManager
            ->getRDBRepository(Job::ENTITY_TYPE)
            ->select([Attribute::ID])
            ->leftJoin('scheduledJob')
            ->where([
                'OR' => [
                    [
                        ['executedAt>=' => $r2From->format($format)],
                        ['executedAt<=' => $r2To->format($format)],
                    ],
                    [
                        ['executeTime>=' => $r1From->format($format)],
                        ['executeTime<='=> $r1To->format($format)],
                        'scheduledJob.job' => 'Dummy',
                    ]
                ]
            ])
            ->findOne();
    }
}

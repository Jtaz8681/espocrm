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

namespace tests\unit\Espo\Tools\Stream;

use Espo\Core\Utils\Config;
use Espo\Entities\Note;
use Espo\Tools\Stream\NoteUtil;
use PHPUnit\Framework\TestCase;

class NoteUtilTest extends TestCase
{
    public function testLink(): void
    {
        $post = "https://site.com/#Account/view/100 https://site.com/#Account/view/100";
        $newPost = "[Account/100](#Account/view/100) [Account/100](#Account/view/100)";

        $this->initText($post, $newPost);

        $post = " https://site.com/#Account/view/100";
        $newPost = " [Account/100](#Account/view/100)";

        $this->initText($post, $newPost);

        $post = "\nhttps://site.com/#Account/view/100";
        $newPost = "\n[Account/100](#Account/view/100)";

        $this->initText($post, $newPost);
    }

    public function testLinkWrapped(): void
    {
        $post = "[Test](https://site.com/#Account/view/100) https://site.com/#Account/view/100";
        $newPost = "[Test](https://site.com/#Account/view/100) [Account/100](#Account/view/100)";

        $this->initText($post, $newPost);
    }

    private function initText(string $post, string $newPost): void
    {
        $config = $this->createMock(Config\ApplicationConfig::class);
        $note = $this->createMock(Note::class);

        $config->expects($this->once())
            ->method('getSiteUrl')
            ->willReturn('https://site.com');

        $util = new NoteUtil($config);

        $note->expects($this->once())
            ->method('getPost')
            ->willReturn($post);

        $note->expects($this->once())
            ->method('setPost')
            ->with($newPost);

        $util->handlePostText($note);
    }
}

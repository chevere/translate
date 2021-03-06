<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests\Translator;

use BadMethodCallException;
use function Chevere\Filesystem\dirForPath;
use Chevere\Filesystem\File;
use Chevere\Translate\PoMaker;
use PHPUnit\Framework\TestCase;

final class PoMakerTest extends TestCase
{
    public function testMakeWithoutScanner(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new PoMaker('en-US', 'messages'))
            ->make(dirForPath(__DIR__ . '/'));
    }

    public function testMakePo(): void
    {
        $locale = 'en-US';
        $makeDir = dirForPath(__DIR__ . "/_resources/make/");
        $poDir = $makeDir->getChild("$locale/");
        $poFile = new File($poDir->path()->getChild("messages.po"));
        $poFile->removeIfExists();
        $dir = dirForPath(__DIR__ . '/_resources/');
        $poMaker = (new PoMaker($locale, 'messages'))
            ->withScanFor($dir->getChild('user/'));
        $poMaker->make($dir->getChild('make/'));
        $this->assertFileExists($poFile->path()->__toString());
        $makeDir->remove();
    }
}

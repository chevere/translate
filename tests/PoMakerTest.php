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

namespace Chevere\Tests;

use BadMethodCallException;
use Chevere\Filesystem\File;
use Chevere\Translate\PoMaker;
use Chevere\Writer\NullWriter;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;

final class PoMakerTest extends TestCase
{
    public function testMakeWithoutScanner(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new PoMaker('en-US', 'messages'))
            ->make(directoryForPath(__DIR__ . '/'));
    }

    public function testMakeWithDomain(): void
    {
        $locale = 'en-US';
        $makeDir = directoryForPath(__DIR__ . '/_resources/make/');
        $poDir = $makeDir->getChild("{$locale}/");
        $poFile = new File($poDir->path()->getChild('messages.po'));
        $poFile->removeIfExists();
        $dir = directoryForPath(__DIR__ . '/_resources/');
        $poMaker = new PoMaker($locale, 'messages');
        $this->assertEquals(new NullWriter(), $poMaker->writer());
        $with = $poMaker->withScanFor(
            $dir->getChild('user/'),
            [
                '_s' => 'gettext',
                '_n' => 'ngettext',
            ]
        );
        $this->assertNotSame($poMaker, $with);
        $with->make($dir->getChild('make/'));
        $this->assertFileExists($poFile->path()->__toString());
        $po = file_get_contents($poFile->path()->__toString());
        $this->assertIsString($po);
        foreach (['var', 'foo', 'js'] as $word) {
            $this->assertStringContainsString("msgid \"{$word}\"", $po);
            $this->assertStringContainsString("msgid \"%d {$word}\"", $po);
            $this->assertStringContainsString("msgid_plural \"%v {$word}s\"", $po);
        }
        $this->assertStringContainsString('user/file.js', $po);
        $jsLines = file($dir->path()->__toString() . 'user/file.js', FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($jsLines);
        foreach (array_keys(array_filter($jsLines, fn ($l) => trim($l) !== '')) as $index) {
            $line = $index + 1;
            $this->assertMatchesRegularExpression("#user/file\\.js:{$line}\\b#", $po);
        }
        $this->assertStringContainsString('msgid "Obj.s"', $po);
        $this->assertStringContainsString('msgid "Obj.n"', $po);
        $this->assertStringContainsString('msgid_plural "Obj.n(s)"', $po);
        $makeDir->remove();
    }

    public function testMakeWithoutDomain(): void
    {
        $locale = 'en';
        $makeDir = directoryForPath(__DIR__ . '/_resources/make/');
        $poFile = new File($makeDir->path()->getChild($locale . '.po'));
        $poFile->removeIfExists();
        $dir = directoryForPath(__DIR__ . '/_resources/');
        $poMaker = new PoMaker($locale);
        $with = $poMaker->withScanFor($dir->getChild('user/'));
        $this->assertNotSame($poMaker, $with);
        $with->make($dir->getChild('make/'));
        $this->assertFileExists($poFile->path()->__toString());
        $makeDir->remove();
    }
}

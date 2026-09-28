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
use Chevere\Writer\StreamWriter;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;
use function Chevere\Writer\streamTemp;

final class PoMakerTest extends TestCase
{
    public function testMakeWithoutScanner(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new PoMaker('en-US', 'messages'))
            ->make(directoryForPath(__DIR__ . '/'));
    }

    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        $locale = 'en-US';
        new PoMaker($locale, 'messages');
    }

    public function testMakeWithDomain(): void
    {
        $locale = 'en-US';
        $makeDirectory = directoryForPath(__DIR__ . '/_resources/make/');
        $poDirectory = $makeDirectory->getChild("{$locale}/");
        $poFile = new File($poDirectory->path()->getChild('messages.po'));
        $poFile->removeIfExists();
        $directory = directoryForPath(__DIR__ . '/_resources/');
        $writer = new StreamWriter(streamTemp());
        $poMaker = new PoMaker($locale, 'messages', writer: $writer);
        $userDirectory = $directory->getChild('user/');
        $with = $poMaker->withScanFor(
            $userDirectory,
            [
                '_s' => 'gettext',
                '_n' => 'ngettext',
            ]
        );
        $this->assertNotSame($poMaker, $with);
        $with->make($directory->getChild('make/'));
        $this->assertFileExists($poFile->path()->__toString());
        $po = file_get_contents($poFile->path()->__toString());
        $this->assertIsString($po);
        foreach (['var', 'foo', 'js'] as $word) {
            $this->assertStringContainsString("msgid \"{$word}\"", $po);
            $this->assertStringContainsString("msgid \"%d {$word}\"", $po);
            $this->assertStringContainsString("msgid_plural \"%v {$word}s\"", $po);
        }
        $this->assertStringContainsString('user/file.js', $po);
        $jsLines = file($directory->path()->__toString() . 'user/file.js', FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($jsLines);
        foreach (array_keys(array_filter($jsLines, fn ($l) => trim($l) !== '')) as $index) {
            $line = $index + 1;
            $this->assertMatchesRegularExpression("#user/file\\.js:{$line}\\b#", $po);
        }
        $this->assertStringContainsString('msgid "Obj.s"', $po);
        $this->assertStringContainsString('msgid "Obj.n"', $po);
        $this->assertStringContainsString('msgid_plural "Obj.n(s)"', $po);
        $this->assertSame(
            <<<PLAIN
            Starting directory scan at {$userDirectory->path()}
            - File {$userDirectory->path()}file.php
            - File {$userDirectory->path()}dir/file.php
            - File {$userDirectory->path()}file.js
            [OK] Directory scan completed
            [OK] PO file generated at {$poFile->path()}

            PLAIN
            ,
            $writer->__toString()
        );
        $makeDirectory->remove();
    }

    public function testMakeWithoutDomain(): void
    {
        $locale = 'en';
        $makeDirectory = directoryForPath(__DIR__ . '/_resources/make/');
        $poFile = new File($makeDirectory->path()->getChild($locale . '.po'));
        $poFile->removeIfExists();
        $directory = directoryForPath(__DIR__ . '/_resources/');
        $poMaker = new PoMaker($locale);
        $with = $poMaker->withScanFor($directory->getChild('user/'));
        $this->assertNotSame($poMaker, $with);
        $with->make($directory->getChild('make/'));
        $this->assertFileExists($poFile->path()->__toString());
        $makeDirectory->remove();
    }
}

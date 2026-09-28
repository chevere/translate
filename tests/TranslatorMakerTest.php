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

use Chevere\Filesystem\Exceptions\DirectoryNotExistsException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Translate\Interfaces\TranslatorMakerInterface;
use Chevere\Translate\TranslatorMaker;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;

final class TranslatorMakerTest extends TestCase
{
    public function testConstructSourceDirNotExists(): void
    {
        $this->expectException(DirectoryNotExistsException::class);
        new TranslatorMaker(
            $this->getDir('404/'),
            $this->getDir('compiled/')
        );
    }

    public function testConstruct(): void
    {
        $sourceDir = $this->getDir('locales/');
        $targetDir = $this->getDir('compiled/');
        $translatorMaker = new TranslatorMaker($sourceDir, $targetDir);
        $this->assertSame($sourceDir, $translatorMaker->sourceDirectory());
        $this->assertSame($targetDir, $translatorMaker->targetDirectory());
    }

    public function testWithLocaleInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getTranslatorMaker()
            ->withMakeTranslation(locale: '404', domain: 'messages');
    }

    public function testMake(): void
    {
        $translatorMaker = $this->getTranslatorMaker();
        $path = $translatorMaker->targetDirectory()
            ->path();
        $domain = 'messages';
        foreach (['en-US', 'es-CL'] as $locale) {
            $file = new File($path->getChild("{$locale}/{$domain}.php"));
            $file->removeIfExists();
            $translatorMaker = $translatorMaker
                ->withMakeTranslation(locale: $locale, domain: $domain);
            $this->assertFileExists($file->path()->__toString());
        }
    }

    private function getTranslatorMaker(): TranslatorMakerInterface
    {
        return new TranslatorMaker($this->getDir('locales/'), $this->getDir('compiled/'));
    }

    private function getDir(string $child): DirectoryInterface
    {
        return directoryForPath(__DIR__ . '/_resources/')->getChild($child);
    }
}

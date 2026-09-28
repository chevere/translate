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
use Chevere\Filesystem\Exceptions\FileNotExistsException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Translate\Interfaces\TranslatorMakerInterface;
use Chevere\Translate\TranslatorMaker;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;

final class TranslatorMakerTest extends TestCase
{
    public function testConstructSourceDirectoryNotExists(): void
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

    public function testWithMakeLocaleDirectoryNotExists(): void
    {
        $this->expectException(DirectoryNotExistsException::class);
        $this->getTranslatorMaker()
            ->withMake(locale: '404', domain: 'messages');
    }

    public function testWithMakeDomainFileNotExists(): void
    {
        $this->expectException(FileNotExistsException::class);
        $this->getTranslatorMaker()
            ->withMake(locale: 'en-US', domain: '404');
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
            $with = $translatorMaker
                ->withMake(locale: $locale, domain: $domain);
            $this->assertNotSame($translatorMaker, $with);
            $this->assertFileExists($file->path()->__toString());
        }
    }

    public function testMakeCreatesTargetLocaleDirectory(): void
    {
        $targetDir = $this->getDir('compiled-tmp/');
        $targetDir->removeIfExists();
        $translatorMaker = new TranslatorMaker($this->getDir('locales/'), $targetDir);
        $localeDir = $targetDir->getChild('en-US/');
        $this->assertFalse($localeDir->exists());

        try {
            $translatorMaker->withMake(locale: 'en-US', domain: 'messages');
            $this->assertTrue($localeDir->exists());
            $this->assertFileExists(
                $localeDir->path()
                    ->getChild('messages.php')
                    ->__toString()
            );
        } finally {
            $targetDir->removeIfExists();
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

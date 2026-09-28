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
use Chevere\Translate\Interfaces\TranslatorBuilderInterface;
use Chevere\Translate\TranslatorBuilder;
use Chevere\Writer\Interfaces\WriterInterface;
use Chevere\Writer\NullWriter;
use Chevere\Writer\StreamWriter;
use Gettext\Loader\PoLoader;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;
use function Chevere\Writer\streamTemp;

final class TranslatorBuilderTest extends TestCase
{
    public function testConstructSourceDirectoryNotExists(): void
    {
        $this->expectException(DirectoryNotExistsException::class);
        new TranslatorBuilder(
            $this->getDirectory('404/'),
            $this->getDirectory('compiled/')
        );
    }

    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        $sourceDirectory = $this->getDirectory('locales/');
        $targetDirectory = $this->getDirectory('compiled/');
        new TranslatorBuilder($sourceDirectory, $targetDirectory);
    }

    public function testWithMakeLocaleDirectoryNotExists(): void
    {
        $this->expectException(DirectoryNotExistsException::class);
        $this->getTranslatorBuilder()
            ->withBuild(locale: '404', domain: 'messages');
    }

    public function testWithMakeDomainFileNotExists(): void
    {
        $this->expectException(FileNotExistsException::class);
        $this->getTranslatorBuilder()
            ->withBuild(locale: 'en-US', domain: '404');
    }

    public function testWithBuild(): void
    {
        $writer = new StreamWriter(streamTemp());
        $translatorBuilder = $this->getTranslatorBuilder(writer: $writer);
        $path = $this->getDirectory('compiled/')
            ->path();
        $domain = 'messages';
        foreach (['en-US', 'es-CL'] as $locale) {
            $file = new File($path->getChild("{$locale}/{$domain}.php"));
            $file->removeIfExists();
            $with = $translatorBuilder
                ->withBuild(locale: $locale, domain: $domain);
            $this->assertNotSame($translatorBuilder, $with);
            $this->assertFileExists($file->path()->__toString());
        }
        $this->assertSame(
            <<<PLAIN
            [OK] {$path}en-US/messages.php
            [OK] {$path}es-CL/messages.php

            PLAIN
            ,
            $writer->__toString()
        );
    }

    public function testWithBuildWithoutDomain(): void
    {
        $sourceDirectory = $this->getDirectory('locales-flat-tmp/');
        $targetDirectory = $this->getDirectory('compiled-flat-tmp/');
        $sourceDirectory->removeIfExists();
        $targetDirectory->removeIfExists();
        $sourceDirectory->createIfNotExists();
        $locales = ['en-US', 'es-CL'];
        foreach ($locales as $locale) {
            copy(
                $this->getDirectory("locales/{$locale}/")
                    ->path()
                    ->getChild('messages.po')
                    ->__toString(),
                $sourceDirectory->path()
                    ->getChild("{$locale}.po")
                    ->__toString()
            );
        }
        $writer = new StreamWriter(streamTemp());
        $translatorBuilder = new TranslatorBuilder(
            $sourceDirectory,
            $targetDirectory,
            new PoLoader(),
            $writer
        );
        $path = $targetDirectory->path();

        try {
            foreach ($locales as $locale) {
                $with = $translatorBuilder->withBuild(locale: $locale);
                $this->assertNotSame($translatorBuilder, $with);
                $this->assertFileExists(
                    $path->getChild("{$locale}.php")
                        ->__toString()
                );
            }
            $this->assertSame(
                <<<PLAIN
                [OK] {$path}en-US.php
                [OK] {$path}es-CL.php

                PLAIN
                ,
                $writer->__toString()
            );
        } finally {
            $sourceDirectory->removeIfExists();
            $targetDirectory->removeIfExists();
        }
    }

    public function testMakeCreatesTargetLocaleDirectory(): void
    {
        $targetDirectory = $this->getDirectory('compiled-tmp/');
        $targetDirectory->removeIfExists();
        $translatorBuilder = new TranslatorBuilder($this->getDirectory('locales/'), $targetDirectory);
        $localeDir = $targetDirectory->getChild('en-US/');
        $this->assertFalse($localeDir->exists());

        try {
            $translatorBuilder->withBuild(locale: 'en-US', domain: 'messages');
            $this->assertTrue($localeDir->exists());
            $this->assertFileExists(
                $localeDir->path()
                    ->getChild('messages.php')
                    ->__toString()
            );
        } finally {
            $targetDirectory->removeIfExists();
        }
    }

    private function getTranslatorBuilder(
        PoLoader $poLoader = new PoLoader(),
        WriterInterface $writer = new NullWriter(),
    ): TranslatorBuilderInterface {
        return new TranslatorBuilder(
            $this->getDirectory('locales/'),
            $this->getDirectory('compiled/'),
            $poLoader,
            $writer
        );
    }

    private function getDirectory(string $child): DirectoryInterface
    {
        return directoryForPath(__DIR__ . '/_resources/')->getChild($child);
    }
}

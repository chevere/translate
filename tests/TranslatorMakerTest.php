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

use function Chevere\Filesystem\dirForPath;
use Chevere\Filesystem\Exceptions\DirNotExistsException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\Interfaces\DirInterface;
use Chevere\Throwable\Exceptions\InvalidArgumentException;
use Chevere\Translate\Interfaces\TranslatorMakerInterface;
use Chevere\Translate\TranslatorMaker;
use PHPUnit\Framework\TestCase;

final class TranslatorMakerTest extends TestCase
{
    public function testConstructSourceDirNotExists(): void
    {
        $this->expectException(DirNotExistsException::class);
        new TranslatorMaker($this->getDir('404/'), $this->getDir('compiled/'));
    }

    public function testConstruct(): void
    {
        $sourceDir = $this->getDir('locales/');
        $targetDir = $this->getDir('compiled/');
        $translatorMaker = new TranslatorMaker($sourceDir, $targetDir);
        $this->assertSame($sourceDir, $translatorMaker->sourceDir());
        $this->assertSame($targetDir, $translatorMaker->targetDir());
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
        $path = $translatorMaker->targetDir()->path();
        $domain = 'messages';
        foreach (['en-US', 'es-CL'] as $locale) {
            $file = new File($path->getChild("${locale}/${domain}.php"));
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

    private function getDir(string $child): DirInterface
    {
        return dirForPath(__DIR__ . '/_resources/')->getChild($child);
    }
}

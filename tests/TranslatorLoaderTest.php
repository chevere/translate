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
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Translate\TranslatorLoader;
use Gettext\Translator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;

final class TranslatorLoaderTest extends TestCase
{
    // public function testConstruct(): void
    // {
    //     $translatorLoader = new TranslatorLoader();
    // }

    public function testGetTranslatorInvalidLocale(): void
    {
        $directory = $this->getDirectory();
        $childDirectory = $directory->getChild('es-404/');
        $this->expectException(DirectoryNotExistsException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            {$childDirectory->path()}
            PLAIN
        );
        (new TranslatorLoader())->withLoad(
            $directory,
            'es-404',
            'messages',
        );
    }

    public function testGetTranslatorInvalidDomain(): void
    {
        $directory = $this->getDirectory();
        $childDirectory = $directory->getChild('es-CL/');
        $this->expectException(FileNotExistsException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            {$childDirectory->path()}invalid.php
            PLAIN
        );
        (new TranslatorLoader())->withLoad($directory, 'es-CL', 'invalid');
    }

    // public function testGetTranslator(): void
    // {
    //     $directory = $this->getDirectory();
    //     $this->assertInstanceOf(
    //         Translator::class,
    //         (new TranslatorLoader())->withLoad($directory, 'es-CL', 'messages')
    //     );
    // }

    // public function testGetTranslatorWithoutDomain(): void
    // {
    //     $loader = new TranslatorLoader(
    //         directoryForPath(__DIR__ . '/_resources/compiled-domainless/')
    //     );
    //     $translator = $loader->getTranslator('es-CL');
    //     $this->assertInstanceOf(Translator::class, $translator);
    //     $this->assertSame('Idiomas', $translator->gettext('Language'));
    // }

    // public function testGetTranslatorWithoutDomainInvalidLocale(): void
    // {
    //     $loader = new TranslatorLoader(
    //         directoryForPath(__DIR__ . '/_resources/compiled-domainless/')
    //     );
    //     $this->expectException(InvalidArgumentException::class);
    //     $loader->getTranslator('es-404');
    // }

    private function getDirectory(): DirectoryInterface
    {
        return directoryForPath(__DIR__ . '/_resources/compiled/');
    }
}

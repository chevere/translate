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
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Translate\TranslatorLoader;
use Gettext\Translator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\directoryForPath;

final class TranslatorLoaderTest extends TestCase
{
    public function testConstructInvalidArgument(): void
    {
        $this->expectException(DirectoryNotExistsException::class);
        new TranslatorLoader(
            directoryForPath(__DIR__ . '/404/')
        );
    }

    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        $dir = directoryForPath(__DIR__ . '/_resources/compiled/');
        new TranslatorLoader($dir);
    }

    public function testGetTranslatorInvalidLocale(): void
    {
        $directory = $this->getDirectory();
        $childDirectory = $directory->getChild('es-404/');
        $loader = new TranslatorLoader($directory);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Directory `{$childDirectory->path()}` doesn't exits
            PLAIN
        );
        $loader->getTranslator('es-404', 'messages');
    }

    public function testGetTranslatorInvalidDomain(): void
    {
        $directory = $this->getDirectory();
        $childDirectory = $directory->getChild('es-CL/');
        $loader = new TranslatorLoader($directory);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            File `{$childDirectory->path()}invalid.php` doesn't exits
            PLAIN
        );
        $loader->getTranslator('es-CL', 'invalid');
    }

    public function testGetTranslator(): void
    {
        $directory = $this->getDirectory();
        $loader = new TranslatorLoader($directory);
        $this->assertInstanceOf(
            Translator::class,
            $loader->getTranslator('es-CL', 'messages')
        );
    }

    public function testGetTranslatorWithoutDomain(): void
    {
        $loader = new TranslatorLoader(
            directoryForPath(__DIR__ . '/_resources/compiled-domainless/')
        );
        $translator = $loader->getTranslator('es-CL');
        $this->assertInstanceOf(Translator::class, $translator);
        $this->assertSame('Idiomas', $translator->gettext('Language'));
    }

    public function testGetTranslatorWithoutDomainInvalidLocale(): void
    {
        $loader = new TranslatorLoader(
            directoryForPath(__DIR__ . '/_resources/compiled-domainless/')
        );
        $this->expectException(InvalidArgumentException::class);
        $loader->getTranslator('es-404');
    }

    private function getDirectory(): DirectoryInterface
    {
        return directoryForPath(__DIR__ . '/_resources/compiled/');
    }
}

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
use Chevere\Translate\Interfaces\TranslatorLoaderInterface;
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
        $loader = $this->getTranslationLoad();
        $this->expectException(InvalidArgumentException::class);
        $loader->getTranslator('es-404', 'messages');
    }

    public function testGetTranslatorInvalidDomain(): void
    {
        $loader = $this->getTranslationLoad();
        $this->expectException(InvalidArgumentException::class);
        $loader->getTranslator('es-CL', 'invalid');
    }

    public function testGetTranslator(): void
    {
        $loader = $this->getTranslationLoad();
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

    private function getTranslationLoad(): TranslatorLoaderInterface
    {
        return new TranslatorLoader(
            directoryForPath(__DIR__ . '/_resources/compiled/')
        );
    }
}

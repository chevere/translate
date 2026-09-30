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

use Chevere\Translate\Translator;
use Chevere\Translate\TranslatorInstance;
use PHPUnit\Framework\TestCase;
use function Chevere\Translate\__;
use function Chevere\Translate\__n;
use function Chevere\Translate\__np;
use function Chevere\Translate\__p;

final class TranslatorFunctionsTest extends TestCase
{
    public function testNullTranslator(): void
    {
        $this->assertSame('Language', __('Language'));
        $this->assertSame('image', __n('image', 'images', 1));
        $this->assertSame('images', __n('image', 'images', 0));
        $this->assertSame('Language', __p('menu', 'Language'));
        $this->assertSame('%d file', __np('upload', '%d file', '%d files', 1));
        $this->assertSame('%d files', __np('upload', '%d file', '%d files', 2));
    }

    public function testTranslator(): void
    {
        $translator = (new Translator())->withAdd(
            [
                '' => [
                    'Language' => 'Idiomas',
                    '%s\'s Images' => 'Imágenes de %s',
                    'image' => [
                        0 => 'imagen',
                        1 => 'imágenes',
                    ],
                    '%d second' => [
                        0 => '%d segundo',
                        1 => '%d segundos',
                    ],
                ],
            ],
        );
        new TranslatorInstance($translator);
        $this->assertSame('Idiomas', __('Language'));
        $this->assertSame('imagen', __n('image', 'images', 1));
        $this->assertSame(
            '2 segundos',
            sprintf(__n('%d second', '%d seconds', 2), 2)
        );
    }

    public function testContextTranslator(): void
    {
        $translator = (new Translator())->withAdd(
            [
                '' => [
                    'Language' => 'Idiomas',
                    '%s\'s Images' => 'Imágenes de %s',
                    'image' => [
                        0 => 'imagen',
                        1 => 'imágenes',
                    ],
                    '%d second' => [
                        0 => '%d segundo',
                        1 => '%d segundos',
                    ],
                ],
                'menu' => [
                    'Language' => 'Idioma',
                ],
                'upload' => [
                    '%d file' => [
                        0 => '%d archivo',
                        1 => '%d archivos',
                    ],
                ],
            ],
        );
        new TranslatorInstance($translator);
        $this->assertSame('Idioma', __p('menu', 'Language'));
        $this->assertSame('Idiomas', __('Language'));
        $this->assertSame('Language', __p('404', 'Language'));
        $this->assertSame('%d archivo', __np('upload', '%d file', '%d files', 1));
        $this->assertSame(
            '2 archivos',
            sprintf(__np('upload', '%d file', '%d files', 2), 2)
        );
        $this->assertSame('%d files', __np('404', '%d file', '%d files', 2));
    }
}

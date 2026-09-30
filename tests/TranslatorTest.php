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
use PHPUnit\Framework\TestCase;
use function Chevere\Filesystem\filePhpReturnForPath;

final class TranslatorTest extends TestCase
{
    public function testWithLoad(): void
    {
        $translator = new Translator();
        $file = filePhpReturnForPath(__DIR__ . '/_resources/compiled/es-CL/messages.php');
        $with = $translator->withLoad($file);
        $this->assertNotSame($translator, $with);
        $this->assertSame('Idiomas', $with->gettext('Language'));
    }

    public function testWithAdd(): void
    {
        $translator = new Translator();
        $add = [
            'domain' => 'domain',
            'pluralForms' => 'nplurals=2; plural=(n != 1);',
            'messages' => [
                '' => [
                    'Language' => 'Lenguas',
                ],
            ],
        ];
        $with = $translator->withAdd(...$add);
        $this->assertNotSame($translator, $with);
        $this->assertSame('Lenguas', $with->gettext('Language'));
        $this->assertSame(
            [
                'domain' => $add['messages'],
            ],
            $with->dictionary()
        );
        $this->assertSame(
            [
                'domain' => [
                    'count' => 2,
                    'code' => 'return ($n != 1);',
                ],
            ],
            $with->plurals()
        );
        $this->assertSame(
            [
                'plurals' => $with->plurals(),
                'dictionary' => $with->dictionary(),
            ],
            [...$with]
        );
    }

    public function testWithLoadOverrideByAdd(): void
    {
        $translator = new Translator();
        $file = filePhpReturnForPath(__DIR__ . '/_resources/compiled/es-CL/messages.php');
        $add = [
            'domain' => '',
            'messages' => [
                '' => [
                    'Language' => 'Lenguas',
                ],
            ],
        ];
        $with = $translator->withLoad($file)
            ->withAdd(...$add);
        $this->assertNotSame($translator, $with);
        $this->assertSame(
            'Lenguas',
            $with->dictionary()['']['']['Language']
        );
    }

    public function testWithAddDomain(): void
    {
        $translator = new Translator();
        $with = $translator->withAdd(
            domain: 'test',
            messages: [
                'menu' => [
                    'Language' => 'Lenguas',
                ],
            ],
        )
            ->withAdd(
                domain: 'alt',
                messages: [
                    'modal' => [
                        'Language' => 'Hablamiento',
                    ],
                ],
            );
        $this->assertNotSame($translator, $with);
        $this->assertSame(
            [
                'test' => [
                    'menu' => [
                        'Language' => 'Lenguas',
                    ],
                ],
                'alt' => [
                    'modal' => [
                        'Language' => 'Hablamiento',
                    ],
                ],
            ],
            $with->dictionary()
        );
    }
}

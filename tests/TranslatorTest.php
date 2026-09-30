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

final class TranslatorTest extends TestCase
{
    public function testWithLoad(): void
    {
        $translator = new Translator();
        $file = __DIR__ . '/_resources/compiled/es-CL/messages.php';
        $with = $translator->withLoad($file);
        $this->assertNotSame($translator, $with);
        $this->assertSame('Idiomas', $with->gettext('Language'));
    }

    public function testWithAdd(): void
    {
        $translator = new Translator();
        $add = [
            // 'domain' => null,
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
                '' => $add['messages'],
            ],
            $with->dictionary()
        );
        $this->assertSame(
            [
                '' => [
                    'count' => 2,
                    'code' => 'return ($n != 1);',
                ],
            ],
            $with->plurals()
        );
    }

    public function testWithLoadOverrideByAdd(): void
    {
        $translator = new Translator();
        $file = __DIR__ . '/_resources/compiled/es-CL/messages.php';
        $add = [
            // 'domain' => null,
            'pluralForms' => 'nplurals=2; plural=(n != 1);',
            'messages' => [
                '' => [
                    'Language' => 'Lenguas',
                ],
            ],
        ];
        $with = $translator->withLoad($file)
            ->withAdd(...$add);
        $this->assertNotSame($translator, $with);
        $this->assertSame('Lenguas', $with->gettext('Language'));
    }
}

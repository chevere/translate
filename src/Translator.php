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

namespace Chevere\Translate;

use Chevere\Translate\Interfaces\TranslatorInterface;
use Gettext\Translator as GettextTranslator;

final class Translator extends GettextTranslator implements TranslatorInterface
{
    public function withLoad(string ...$files): TranslatorInterface
    {
        $new = clone $this;
        $new->loadTranslations(...$files);

        return $new;
    }

    public function withAdd(array $messages, string $pluralForms = '', string $domain = ''): TranslatorInterface
    {
        $new = clone $this;
        $new->addTranslations(
            [
                'domain' => $domain,
                'plural-forms' => $pluralForms,
                'messages' => $messages,
            ]
        );

        return $new;
    }

    public function dictionary(): array
    {
        return $this->dictionary;
    }

    public function plurals(): array
    {
        return $this->plurals;
    }
}

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

use Chevere\Filesystem\Interfaces\FilePhpReturnInterface;
use Chevere\Translate\Interfaces\TranslatorInterface;
use Gettext\Translator as GettextTranslator;
use InvalidArgumentException;

final class Translator extends GettextTranslator implements TranslatorInterface
{
    public function withLoad(FilePhpReturnInterface ...$file): TranslatorInterface
    {
        $new = clone $this;
        foreach ($file as $item) {
            $translations = $item->get();
            if (! is_array($translations)) {
                $type = gettype($translations);

                throw new InvalidArgumentException(
                    <<<PLAIN
                    Translations must be an array, {$type} given
                    PLAIN
                );
            }
            $new->addTranslations($translations);
        }

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

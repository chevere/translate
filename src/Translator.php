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
use Traversable;

final class Translator extends GettextTranslator implements TranslatorInterface
{
    /**
     * @var array<string, string>
     */
    private array $pluralForms = [];

    /**
     * @param array<mixed> $translations
     */
    public function addTranslations(array $translations): self
    {
        $domain = $translations['domain'] ?? '';
        $pluralForms = $translations['plural-forms'] ?? '';
        if (is_string($domain)
            && is_string($pluralForms)
            && $pluralForms !== ''
            && ! isset($this->dictionary[$domain])
        ) {
            $this->pluralForms[$domain] = $pluralForms;
        }
        parent::addTranslations($translations);

        return $this;
    }

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

    public function domain(): string
    {
        return $this->domain ?? '';
    }

    public function dictionary(): array
    {
        return $this->dictionary;
    }

    public function pluralForms(): array
    {
        return $this->pluralForms;
    }

    public function getIterator(): Traversable
    {
        yield 'domain' => $this->domain();
        yield 'plural-forms' => $this->pluralForms;
        yield 'dictionary' => $this->dictionary;
    }
}

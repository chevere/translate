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

namespace Chevere\Translate\Interfaces;

use Chevere\Filesystem\Interfaces\FilePhpReturnInterface;
use Gettext\TranslatorInterface as GettextTranslatorInterface;
use IteratorAggregate;
use Traversable;

/**
 * Describes the component in charge of providing translator tooling.
 *
 * @extends IteratorAggregate<'domain'|'plural-forms'|'plurals'|'dictionary', string|array<string, string>|array<string, array{count: int, code: string}>|array<string, array<string, array<string, string|array<string>>>>>
 */
interface TranslatorInterface extends GettextTranslatorInterface, IteratorAggregate
{
    /**
     * Load new translations from php files
     */
    public function withLoad(FilePhpReturnInterface ...$file): self;

    /**
     * Add new translations to the dictionary.
     *
     * @param array<string, array<string, mixed>> $messages The messages to add.
     */
    public function withAdd(array $messages, string $pluralForms = '', string $domain = ''): self;

    /**
     * @return string The default domain.
     */
    public function domain(): string;

    /**
     * @return array<string, array<string, array<string, string|array<string>>>> Translations indexed by domain => context => original => translation(s).
     */
    public function dictionary(): array;

    /**
     * @return array<string, array{count: int, code: string}> Plural rules indexed by domain.
     */
    public function plurals(): array;

    /**
     * @return array<string, string> Plural-Forms as defined in the .po header, indexed by domain.
     */
    public function pluralForms(): array;

    /**
     * Yields `domain` => string, `plurals` => plurals(), `plural-forms` => pluralForms(), `dictionary` => dictionary().
     */
    public function getIterator(): Traversable;
}

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

use Gettext\TranslatorInterface as GettextTranslatorInterface;

/**
 * Describes the component in charge of providing translator tooling.
 */
interface TranslatorInterface extends GettextTranslatorInterface
{
    /**
     * Load new translations from php files
     */
    public function withLoad(string ...$files): self;

    /**
     * Add new translations to the dictionary.
     *
     * @param array<string, array<string, mixed>> $messages The messages to add.
     */
    public function withAdd(array $messages, string $pluralForms = '', string $domain = ''): self;

    /**
     * @return array{string, array<string, array<string, mixed>>} Dictionary indexed by domain.
     */
    public function dictionary(): array;

    /**
     * @return array<string, string> Plural forms indexed by domain.
     */
    public function plurals(): array;
}

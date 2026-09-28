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

use Gettext\Translator;
use Gettext\TranslatorInterface;
use LogicException;

function getTranslator(): TranslatorInterface
{
    try {
        return TranslatorInstance::get();
    } catch (LogicException) {
        return new Translator();
    }
}

/**
 * Translates a string.
 */
function __(string $message): string
{
    return getTranslator()->gettext($message);
}
/**
 * Translates a formatted string with `sprintf`.
 */
function __f(string $message, bool|float|int|string|null ...$values): string
{
    return sprintf(__($message), ...$values);
}
/**
 * Translates a formatted string with `strtr`.
 *
 * @param array<string, string> $fromTo
 */
function __t(string $message, array $fromTo = []): string
{
    return strtr(__($message), $fromTo);
}
/**
 * Translates a formatted plural string.
 */
function __n(string $singular, string $plural, int $count): string
{
    return getTranslator()->ngettext($singular, $plural, $count);
}
/**
 * Translates a formatted plural string with `sprintf`.
 */
function __nf(string $singular, string $plural, int $count, bool|float|int|string|null ...$values): string
{
    return sprintf(
        __n($singular, $plural, $count),
        ...$values
    );
}
/**
 * Translates a formatted plural string with `strtr`.
 *
 * @param array<string, string> $replacePairs
 */
function __nt(string $singular, string $plural, int $count, array $replacePairs): string
{
    return strtr(__n($singular, $plural, $count), $replacePairs);
}

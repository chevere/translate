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

function translator(): TranslatorInterface
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
    return translator()->gettext($message);
}
/**
 * Translates a plural string.
 */
function __n(string $singular, string $plural, int $count): string
{
    return translator()->ngettext($singular, $plural, $count);
}
/**
 * Translates a string checking its context.
 */
function __p(string $context, string $message): string
{
    return translator()->pgettext($context, $message);
}
/**
 * Translates a plural string checking its context.
 */
function __np(string $context, string $singular, string $plural, int $count): string
{
    return translator()->npgettext($context, $singular, $plural, $count);
}
/**
 * Translates a string within a specific domain.
 */
function __d(string $domain, string $message): string
{
    return translator()->dgettext($domain, $message);
}
/**
 * Translates a plural string within a specific domain.
 */
function __dn(string $domain, string $singular, string $plural, int $count): string
{
    return translator()->dngettext($domain, $singular, $plural, $count);
}
/**
 * Translates a string within a specific domain and context.
 */
function __dp(string $domain, string $context, string $message): string
{
    return translator()->dpgettext($domain, $context, $message);
}
/**
 * Translates a plural string within a specific domain and context.
 */
function __dnp(string $domain, string $context, string $singular, string $plural, int $count): string
{
    return translator()->dnpgettext($domain, $context, $singular, $plural, $count);
}

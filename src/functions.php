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

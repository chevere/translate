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

use Chevere\Filesystem\Interfaces\DirectoryInterface;
use DomainException;
use Gettext\TranslatorInterface;
use InvalidArgumentException;
use LogicException;

/**
 * Describes the component in charge of load php translations.
 */
interface TranslatorLoaderInterface
{
    public function directory(): DirectoryInterface;

    /**
     * @throws InvalidArgumentException If $locale doesn't exists.
     * @throws DomainException If $domain doesn't exists.
     * @throws LogicException If unable to load translator.
     */
    public function getTranslator(string $locale, string $domain): TranslatorInterface;
}

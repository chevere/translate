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

/**
 * Describes the component in charge of make a translator.
 */
interface TranslatorMakerInterface
{
    public function sourceDirectory(): DirectoryInterface;

    public function targetDirectory(): DirectoryInterface;

    public function withMakeTranslation(string $locale, string $domain): self;
}

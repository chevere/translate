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

use Chevere\Filesystem\File;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Translate\Interfaces\TranslatorLoaderInterface;
use DomainException;
use Gettext\Translator;
use Gettext\TranslatorInterface;
use InvalidArgumentException;

final class TranslatorLoader implements TranslatorLoaderInterface
{
    public function __construct(
        private DirectoryInterface $directory
    ) {
        $this->directory->assertExists();
    }

    public function directory(): DirectoryInterface
    {
        return $this->directory;
    }

    public function getTranslator(string $locale, string $domain): TranslatorInterface
    {
        $dir = $this->directory->getChild($locale . '/');
        if (! $dir->exists()) {
            throw new InvalidArgumentException(
                sprintf("Locale `%s` doesn't exits", $locale)
            );
        }
        $file = new File(
            $dir->path()
                ->getChild("${domain}.php")
        );
        if (! $file->exists()) {
            throw new DomainException(
                sprintf("Domain `%s` doesn't exits", $domain)
            );
        }

        return (new Translator())
            ->loadTranslations($file->path()->__toString());
    }
}

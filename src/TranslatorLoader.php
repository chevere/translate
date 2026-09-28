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

    public function getTranslator(string $locale, string $domain = ''): TranslatorInterface
    {
        $directory = match ($domain) {
            '' => $this->directory,
            default => $this->directory->getChild($locale . '/'),
        };
        if (! $directory->exists()) {
            throw new InvalidArgumentException(
                sprintf("Directory `%s` doesn't exits", $locale)
            );
        }
        $filename = match ($domain) {
            '' => "{$locale}.php",
            default => "{$domain}.php",
        };
        $file = new File(
            $directory->path()
                ->getChild($filename)
        );
        if (! $file->exists()) {
            throw new DomainException(
                sprintf("File `%s` doesn't exits", $filename)
            );
        }

        return (new Translator())
            ->loadTranslations($file->path()->__toString());
    }
}

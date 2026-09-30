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
use Chevere\Translate\Interfaces\TranslatorInterface;

final class TranslatorLoader
{
    private TranslatorInterface $translator;

    public function __construct(
    ) {
        $this->translator = new Translator();
    }

    public function withLoad(
        DirectoryInterface $directory,
        string $locale,
        string $domain = ''
    ): self {
        $new = clone $this;
        $directory = match ($domain) {
            '' => $directory,
            default => $directory->getChild($locale . '/'),
        };
        $directory->assertExists();
        $filename = match ($domain) {
            '' => "{$locale}.php",
            default => "{$domain}.php",
        };
        $file = new File(
            $directory->path()
                ->getChild($filename)
        );
        $file->assertExists();
        $new->translator = $new->translator->withLoad($file->path()->__toString());

        return $new;
    }

    public function translator(): TranslatorInterface
    {
        return $this->translator;
    }
}

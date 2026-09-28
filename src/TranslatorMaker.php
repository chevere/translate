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
use Chevere\Translate\Interfaces\TranslatorMakerInterface;
use Gettext\Generator\ArrayGenerator;
use Gettext\Loader\PoLoader;

final class TranslatorMaker implements TranslatorMakerInterface
{
    private DirectoryInterface $localeSourceDir;

    private DirectoryInterface $localeTargetDir;

    public function __construct(
        private DirectoryInterface $sourceDir,
        private DirectoryInterface $targetDir,
        private PoLoader $poLoader = new PoLoader(),
    ) {
        $this->sourceDir->assertExists();
    }

    public function sourceDirectory(): DirectoryInterface
    {
        return $this->sourceDir;
    }

    public function targetDirectory(): DirectoryInterface
    {
        return $this->targetDir;
    }

    public function withMake(string $locale, string $domain): self
    {
        $new = clone $this;
        $new->handleLocale($locale);
        $poFile = new File(
            $new->localeSourceDir->path()
                ->getChild("{$domain}.po")
        );
        $poFile->assertExists();
        $translations = $new->poLoader->loadFile($poFile->path()->__toString());
        $new->localeTargetDir->createIfNotExists();
        $phpFile = new File(
            $new->localeTargetDir->path()
                ->getChild("{$domain}.php")
        );
        $phpFile->removeIfExists();
        (new ArrayGenerator())
            ->generateFile($translations, $phpFile->path()->__toString());
        $phpFile->assertExists();

        return $new;
    }

    private function handleLocale(string $locale): void
    {
        $this->localeSourceDir = $this->sourceDir->getChild($locale . '/');
        $this->localeSourceDir->assertExists();
        $this->localeTargetDir = $this->targetDir->getChild($locale . '/');
    }
}

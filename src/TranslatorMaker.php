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
use InvalidArgumentException;
use LogicException;
use Throwable;

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

    public function withMakeTranslation(string $locale, string $domain): self
    {
        $new = clone $this;
        $new->handleLocale($locale);
        $new->localeSourceDir->assertExists();
        $poFile = new File(
            $new->localeSourceDir->path()
                ->getChild("{$domain}.po")
        );
        $poFile->assertExists();

        try {
            $translations = $new->poLoader->loadFile($poFile->path()->__toString());
        } catch (Throwable $e) {
            throw new LogicException(
                message: 'Unable to load translations',
                previous: $e,
            );
        }
        $new->localeTargetDir->createIfNotExists();
        $phpFile = new File(
            $new->localeTargetDir->path()
                ->getChild("{$domain}.php")
        );
        $phpFile->removeIfExists();

        try {
            (new ArrayGenerator())
                ->generateFile($translations, $phpFile->path()->__toString());
        } catch (Throwable $e) {
            throw new LogicException(
                message: 'Unable to generate translations',
                previous: $e,
            );
        }
        $phpFile->assertExists();

        return $new;
    }

    private function handleLocale(string $locale): void
    {
        $this->localeSourceDir = $this->sourceDir->getChild($locale . '/');

        try {
            $this->localeSourceDir->assertExists();
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                message: sprintf('Invalid locale `%s` provided', $locale),
                previous: $e,
            );
        }
        $this->localeTargetDir = $this->targetDir->getChild($locale . '/');
    }
}

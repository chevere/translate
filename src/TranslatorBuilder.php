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
use Chevere\Translate\Interfaces\TranslatorBuilderInterface;
use Chevere\Writer\Interfaces\WriterInterface;
use Chevere\Writer\NullWriter;
use Gettext\Generator\ArrayGenerator;
use Gettext\Loader\PoLoader;

final class TranslatorBuilder implements TranslatorBuilderInterface
{
    private DirectoryInterface $localeSourceDirectory;

    private DirectoryInterface $localeTargetDirectory;

    public function __construct(
        private DirectoryInterface $sourceDirectory,
        private DirectoryInterface $targetDirectory,
        private PoLoader $poLoader = new PoLoader(),
        private WriterInterface $writer = new NullWriter()
    ) {
        $this->sourceDirectory->assertExists();
    }

    public function withBuild(string $locale, string $domain = ''): self
    {
        $new = clone $this;
        $new->handleLocale($locale, $domain);
        $poFilename = match ($domain) {
            '' => "{$locale}.po",
            default => "{$domain}.po"
        };
        $poFile = new File(
            $new->localeSourceDirectory->path()
                ->getChild($poFilename)
        );
        $poFile->assertExists();
        $translations = $new->poLoader->loadFile($poFile->path()->__toString());
        $new->localeTargetDirectory->createIfNotExists();
        $phpFilename = match ($domain) {
            '' => "{$locale}.php",
            default => "{$domain}.php"
        };
        $phpFile = new File(
            $new->localeTargetDirectory->path()
                ->getChild($phpFilename)
        );
        (new ArrayGenerator())
            ->generateFile($translations, $phpFile->path()->__toString());
        /**
         * @infection-ignore-all
         */
        $phpFile->assertExists();
        $this->writer->write(
            <<<PLAIN
            [OK] {$phpFile->path()}

            PLAIN
        );

        return $new;
    }

    private function handleLocale(string $locale, string $domain): void
    {
        $this->localeSourceDirectory = match ($domain) {
            '' => $this->sourceDirectory,
            default => $this->sourceDirectory->getChild($locale . '/')
        };
        $this->localeSourceDirectory->assertExists();
        $this->localeTargetDirectory = match ($domain) {
            '' => $this->targetDirectory,
            default => $this->targetDirectory->getChild($locale . '/')
        };
    }
}

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

use BadMethodCallException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Iterator\RecursiveFileFilterIterator;
use Chevere\Translate\Interfaces\PoMakerInterface;
use Chevere\Writer\Interfaces\WriterInterface;
use Chevere\Writer\NullWriter;
use Gettext\Generator\PoGenerator;
use Gettext\Scanner\PhpScanner;
use Gettext\Translations;
use InvalidArgumentException;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

final class PoMaker implements PoMakerInterface
{
    public const FUNCTIONS = [
        '__' => 'gettext',
        '__f' => 'gettext',
        '__t' => 'gettext',
        '__n' => 'ngettext',
        '__nf' => 'ngettext',
        '__nt' => 'ngettext',
    ];

    private DirectoryInterface $sourceDir;

    private PhpScanner $phpScanner;

    public function __construct(
        private string $locale,
        private string $domain,
        private WriterInterface $writer = new NullWriter()
    ) {
    }

    public function writer(): WriterInterface
    {
        return $this->writer;
    }

    public function withScanFor(DirectoryInterface $sourceDirectory): PoMakerInterface
    {
        $new = clone $this;
        $sourceDirectory->assertExists();
        $new->sourceDir = $sourceDirectory;
        $new->phpScanner = new PhpScanner(Translations::create($new->domain));
        $new->phpScanner->setDefaultDomain($new->domain);
        $new->phpScanner->setFunctions(self::FUNCTIONS);
        $iterator = $new->getIterator();
        $this->writer->write(
            sprintf("📂 Starting dir %s iteration\n", $new->sourceDir->path()->__toString())
        );
        $iterator->rewind();
        while ($iterator->valid()) {
            /** @var SplFileInfo $file */
            $file = $iterator->current();
            $pathName = $file->getPathname();
            $new->writer->write("- File {$pathName}\n");

            try {
                $new->phpScanner->scanFile($pathName);
            } catch (Throwable $e) {
                throw new LogicException(
                    message: 'Unable to scan file',
                    previous: $e,
                );
            }
            $iterator->next();
        }
        $this->writer->write("💯 Done!\n");

        return $new;
    }

    public function make(DirectoryInterface $targetDirectory): void
    {
        if (! isset($this->phpScanner)) {
            throw new BadMethodCallException(
                sprintf(
                    'Unable to call `%s` without a `%s` instance',
                    __METHOD__,
                    PhpScanner::class
                )
            );
        }
        $generator = new PoGenerator();
        $targetDirectory = $targetDirectory->getChild($this->locale . '/');
        $targetDirectory->createIfNotExists();
        $poFile = new File($targetDirectory->path()->getChild($this->domain . '.po'));
        $poFile->removeIfExists();
        /**
         * @var Translations $translations
         */
        foreach ($this->phpScanner->getTranslations() as $translations) {
            $translations->setLanguage($this->locale);

            try {
                $generator->generateFile($translations, $poFile->path()->__toString());
            } catch (InvalidArgumentException $e) {
                throw new LogicException(
                    message: 'Unable to make translation',
                    previous: $e,
                );
            }

            break;
        }
    }

    /**
     * @return RecursiveIteratorIterator<RecursiveFileFilterIterator>
     */
    private function getIterator(): RecursiveIteratorIterator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveFileFilterIterator(
                new RecursiveDirectoryIterator(
                    $this->sourceDir->path()
                        ->__toString(),
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                '.php'
            )
        );
        $iterator->rewind();

        return $iterator;
    }
}

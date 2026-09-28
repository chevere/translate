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
use Gettext\Scanner\CodeScanner;
use Gettext\Scanner\JsScanner;
use Gettext\Scanner\PhpScanner;
use Gettext\Translations;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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

    private DirectoryInterface $directory;

    private Translations $translations;

    public function __construct(
        private string $locale,
        private string $domain = '',
        private WriterInterface $writer = new NullWriter()
    ) {
    }

    public function withScanFor(
        DirectoryInterface $directory,
        array $functions = []
    ): PoMakerInterface {
        $new = clone $this;
        $directory->assertExists();
        $new->directory = $directory;
        $new->translations = Translations::create($new->domain);
        $scanners = [
            '.php' => new PhpScanner($new->translations),
            '.js' => new JsScanner($new->translations),
        ];
        $new->writer->write(
            <<<PLAIN
            Starting directory scan at {$new->directory->path()}

            PLAIN
        );
        foreach ($scanners as $extension => $scanner) {
            $new->scan($scanner, $extension, $functions);
        }
        $new->writer->write(
            <<<PLAIN
            [OK] Directory scan completed

            PLAIN
        );

        return $new;
    }

    public function make(DirectoryInterface $targetDirectory): void
    {
        if (! isset($this->translations)) {
            throw new BadMethodCallException(
                sprintf(
                    'Unable to call `%s` without calling `withScanFor` first',
                    __METHOD__
                )
            );
        }
        $targetDirectory = match ($this->domain) {
            '' => $targetDirectory,
            default => $targetDirectory->getChild($this->locale . '/'),
        };
        $filename = match (true) {
            $this->domain === '' => $this->locale . '.po',
            default => $this->domain . '.po',
        };
        $targetDirectory->createIfNotExists();
        $poFile = new File(
            $targetDirectory->path()
                ->getChild($filename)
        );
        $translations = $this->translations->setLanguage($this->locale);
        (new PoGenerator())->generateFile($translations, $poFile->path()->__toString());
        /**
         * @infection-ignore-all
         */
        $poFile->assertExists();
        $this->writer->write(
            <<<PLAIN
            [OK] PO file generated at {$poFile->path()}

            PLAIN
        );
    }

    /**
     * @param array<string, string> $functions
     */
    private function scan(CodeScanner $scanner, string $extension, array $functions = []): void
    {
        $scanner->setDefaultDomain($this->domain);
        $scanner->setFunctions(
            array_merge(self::FUNCTIONS, $functions)
        );
        $iterator = $this->getIterator($extension);
        while ($iterator->valid()) {
            /** @var SplFileInfo $file */
            $file = $iterator->current();
            $pathName = $file->getPathname();
            $this->writer->write("- File {$pathName}\n");
            $scanner->scanFile($pathName);
            $iterator->next();
        }
    }

    /**
     * @return RecursiveIteratorIterator<RecursiveFileFilterIterator>
     */
    private function getIterator(string $extension): RecursiveIteratorIterator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveFileFilterIterator(
                new RecursiveDirectoryIterator(
                    $this->directory->path()
                        ->__toString(),
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                $extension
            )
        );
        $iterator->rewind();

        return $iterator;
    }
}

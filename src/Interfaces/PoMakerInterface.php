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

use BadMethodCallException;
use Chevere\Filesystem\Exceptions\DirectoryNotExistsException;
use Chevere\Filesystem\Exceptions\DirectoryUnableToCreateException;
use Chevere\Filesystem\Exceptions\FileUnableToRemoveException;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Writer\Interfaces\WriterInterface;
use InvalidArgumentException;
use LogicException;

/**
 * Describes the component in charge of providing a `.po` maker.
 */
interface PoMakerInterface
{
    /**
     * @throws DirectoryNotExistsException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function withScanFor(DirectoryInterface $sourceDirectory): self;

    /**
     * @throws BadMethodCallException If called without scanner.
     * @throws DirectoryUnableToCreateException If unable to create the target dir (if doesn't exists).
     * @throws FileUnableToRemoveException If unable to remove existing `.po` at target dir.
     * @throws LogicException If unable to create the translation file.
     */
    public function make(DirectoryInterface $targetDirectory): void;

    public function writer(): WriterInterface;
}

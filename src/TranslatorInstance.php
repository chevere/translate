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

use Gettext\TranslatorInterface;
use LogicException;

final class TranslatorInstance
{
    private static TranslatorInterface $instance;

    public function __construct(TranslatorInterface $translator)
    {
        self::$instance = $translator;
    }

    public static function get(): TranslatorInterface
    {
        if (! isset(self::$instance)) {
            throw new LogicException('No Translator instance present');
        }

        return self::$instance;
    }
}

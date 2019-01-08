<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces\Layout\Base;

interface PrintableLayoutInterface extends LayoutInterface
{
    /**
     * register a callable which prints to the document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * ensure the cursor is below the printed content after the callable is finished to not mess up the layout.
     *
     * @param callable $callable the arguments are decided by the layout implementation. At least the documents to print to should be included.
     */
    public function registerPrintable(callable $callable);
}

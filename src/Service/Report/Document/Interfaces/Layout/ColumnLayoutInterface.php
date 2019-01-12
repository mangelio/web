<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces\Layout;

use App\Service\Report\Document\Interfaces\Layout\Base\ColumnedLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\Base\PrintableLayoutInterface;

interface ColumnLayoutInterface extends ColumnedLayoutInterface, PrintableLayoutInterface
{
    /**
     * when printing something, the column with the least content is chosen automatically.
     *
     * @param bool $active
     */
    public function setAutoColumn(bool $active);
}

<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Foyer;

use App\Api\Entity\Foyer\NumberIssue;
use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Issue;

class NumberIssueTransformer extends BatchTransformer
{
    /**
     * @param Issue $entity
     *
     * @return NumberIssue
     */
    public function toApi($entity)
    {
        $issue = new NumberIssue($entity->getId());
        $issue->setNumber($entity->getNumber());

        return $issue;
    }
}

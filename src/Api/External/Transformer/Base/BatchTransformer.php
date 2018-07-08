<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer\Base;

abstract class BatchTransformer
{
    abstract public function toApi($entity, $args = null);

    /**
     * transforms all toApi, preserving the ordering.
     *
     * @param array $entities
     * @param null $args
     *
     * @return array
     */
    public function toApiMultiple(array $entities, $args = null)
    {
        $res = [];
        foreach ($entities as $entity) {
            $res[] = $this->toApi($entity, $args);
        }

        return $res;
    }
}

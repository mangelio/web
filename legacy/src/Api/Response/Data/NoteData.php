<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Base\Note;

class NoteData
{
    /**
     * @var Note
     */
    private $note;

    public function getNote(): Note
    {
        return $this->note;
    }

    public function setNote(Note $note): void
    {
        $this->note = $note;
    }
}
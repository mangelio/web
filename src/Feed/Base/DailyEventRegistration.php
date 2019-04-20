<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Feed\Base;

use DateTime;
use Generator;

class DailyEventRegistration
{
    /**
     * @var int[][]
     */
    private $counts = [];

    /**
     * @var DateTime[]
     */
    private $timeNormalization = [];

    /**
     * @param DateTime $time
     *
     * @return string
     */
    protected function normalizeTime(DateTime $time)
    {
        return $time->format('Y.m.d');
    }

    /**
     * @var mixed[]
     */
    private $receivers = [];

    /**
     * @param $receiver
     *
     * @return string
     */
    protected function normalizeReceiver($receiver)
    {
        //get existing receiver & return it if found
        $res = array_search($receiver, $this->receivers, true);
        if ($res) {
            return $res;
        }

        //create new receiver
        $this->receivers[] = $receiver;

        return \count($this->receivers) - 1;
    }

    /**
     * @param DateTime $time
     * @param mixed $receiver
     */
    protected function register(DateTime $time, $receiver)
    {
        $timeKey = $this->normalizeTime($time);
        if (!isset($this->counts[$timeKey])) {
            $this->counts[$timeKey] = [];
            $this->timeNormalization[$timeKey] = $time;
        }

        $receiverKey = $this->normalizeReceiver($receiver);
        if (!isset($this->counts[$timeKey][$receiverKey])) {
            $this->counts[$timeKey][$receiverKey] = 0;
        }

        ++$this->counts[$timeKey][$receiverKey];
    }

    /**
     * @return Generator
     */
    protected function getRegistrations()
    {
        foreach ($this->counts as $timeKey => $entries) {
            foreach ($entries as $receiverKey => $count) {
                yield [$this->timeNormalization[$timeKey], $this->receivers[$receiverKey], $count];
            }
        }
    }
}

<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

class CraftsmanStatistics
{
    /**
     * @var string
     */
    private $craftsman;

    /**
     * @var int
     */
    public $issueOpenCount = 0;

    /**
     * @var int
     */
    public $issueUnreadCount = 0;

    /**
     * @var int
     */
    public $issueOverdueCount = 0;

    /**
     * @var int
     */
    public $issueClosedCount = 0;

    /**
     * @var string|null
     */
    public $nextDeadline;

    /**
     * @var \DateTime|null
     */
    public $lastEmailReceived;

    /**
     * @var \DateTime|null
     */
    public $lastVisitOnline;

    /**
     * @var \DateTime|null
     */
    public $lastIssueResolved;

    public function getCraftsman(): string
    {
        return $this->craftsman;
    }

    public function setCraftsman(string $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    public function getIssueOpenCount(): int
    {
        return $this->issueOpenCount;
    }

    public function setIssueOpenCount(int $issueOpenCount): void
    {
        $this->issueOpenCount = $issueOpenCount;
    }

    public function getIssueUnreadCount(): int
    {
        return $this->issueUnreadCount;
    }

    public function setIssueUnreadCount(int $issueUnreadCount): void
    {
        $this->issueUnreadCount = $issueUnreadCount;
    }

    public function getIssueOverdueCount(): int
    {
        return $this->issueOverdueCount;
    }

    public function setIssueOverdueCount(int $issueOverdueCount): void
    {
        $this->issueOverdueCount = $issueOverdueCount;
    }

    public function getIssueClosedCount(): int
    {
        return $this->issueClosedCount;
    }

    public function setIssueClosedCount(int $issueClosedCount): void
    {
        $this->issueClosedCount = $issueClosedCount;
    }

    public function getNextDeadline(): ?string
    {
        return $this->nextDeadline;
    }

    public function setNextDeadline(?string $nextDeadline): void
    {
        $this->nextDeadline = $nextDeadline;
    }

    public function getLastEmailReceived(): ?\DateTime
    {
        return $this->lastEmailReceived;
    }

    public function setLastEmailReceived(?\DateTime $lastEmailReceived): void
    {
        $this->lastEmailReceived = $lastEmailReceived;
    }

    public function getLastVisitOnline(): ?\DateTime
    {
        return $this->lastVisitOnline;
    }

    public function setLastVisitOnline(?\DateTime $lastVisitOnline): void
    {
        $this->lastVisitOnline = $lastVisitOnline;
    }

    public function getLastIssueResolved(): ?\DateTime
    {
        return $this->lastIssueResolved;
    }

    public function setLastIssueResolved(?\DateTime $lastIssueResolved): void
    {
        $this->lastIssueResolved = $lastIssueResolved;
    }
}

<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Share\Craftsman;

class Craftsman extends \App\Api\Entity\Base\Craftsman
{
    /**
     * @var string
     */
    private $reportUrl;

    /**
     * @var bool
     */
    private $canRespondToIssues;

    /**
     * @var string
     */
    private $readOnlyViewUrl;

    public function getReportUrl(): string
    {
        return $this->reportUrl;
    }

    public function setReportUrl(string $reportUrl): void
    {
        $this->reportUrl = $reportUrl;
    }

    public function getReadOnlyViewUrl(): string
    {
        return $this->readOnlyViewUrl;
    }

    public function setReadOnlyViewUrl(string $readOnlyViewUrl): void
    {
        $this->readOnlyViewUrl = $readOnlyViewUrl;
    }

    public function getCanRespondToIssues(): bool
    {
        return $this->canRespondToIssues;
    }

    public function setCanRespondToIssues(bool $canRespondToIssues): void
    {
        $this->canRespondToIssues = $canRespondToIssues;
    }
}

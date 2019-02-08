<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Pdf\Configuration;

class Color
{
    /**
     * @var string
     */
    private $textColor = '#252820';

    /**
     * @var string
     */
    private $secondaryTextColor = '#44493a';

    /**
     * the color of lines & others.
     *
     * @var string
     */
    private $drawColor = '#c8c8c8';

    /**
     * @var string
     */
    private $background = '#e6e6e6';

    /**
     * @var string
     */
    private $secondaryBackground = '#f0f0f0';

    /**
     * @return string
     */
    public function getTextColor(): string
    {
        return $this->textColor;
    }

    /**
     * @return string
     */
    public function getSecondaryTextColor(): string
    {
        return $this->secondaryTextColor;
    }

    /**
     * @return string
     */
    public function getDrawColor(): string
    {
        return $this->drawColor;
    }

    /**
     * @return string
     */
    public function getBackground(): string
    {
        return $this->background;
    }

    /**
     * @return string
     */
    public function getSecondaryBackground(): string
    {
        return $this->secondaryBackground;
    }
}
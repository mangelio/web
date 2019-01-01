<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document\Layout\Base;

use App\Service\Report\Document\PrinterInterface;
use App\Service\Report\Pdf\Document\Printer;

class BaseLayout implements PrinterInterface
{
    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var float
     */
    private $defaultWidth;

    /**
     * BaseLayout constructor.
     *
     * @param Printer $printer
     * @param float $defaultWidth
     */
    public function __construct(Printer $printer, float $defaultWidth)
    {
        $this->printer = $printer;
        $this->defaultWidth = $defaultWidth;
    }

    /**
     * @param string $title
     */
    public function printTitle(string $title)
    {
        $this->printer->printTitle($title, $this->defaultWidth);
    }

    /**
     * @param string $paragraph
     */
    public function printParagraph(string $paragraph)
    {
        $this->printer->printParagraph($paragraph, $this->defaultWidth);
    }

    /**
     * @param string[] $keyValues
     */
    public function printKeyValueParagraph(array $keyValues)
    {
        $this->printer->printKeyValueParagraph($keyValues, $this->defaultWidth);
    }

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header)
    {
        $this->printer->printRegionHeader($header, $this->defaultWidth);
    }

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printTable(array $header, array $content)
    {
        $this->printer->printTable($header, $content, $this->defaultWidth);
    }

    /**
     * @param string $filePath
     */
    public function printImage(string $filePath)
    {
        $this->printer->printImage($filePath, $this->defaultWidth);
    }
}

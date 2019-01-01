<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document;

interface PrinterInterface
{
    /**
     * @param string $title
     */
    public function printTitle(string $title);

    /**
     * @param string $paragraph
     */
    public function printParagraph(string $paragraph);

    /**
     * @param string[] $keyValues
     */
    public function printKeyValueParagraph(array $keyValues);

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header);

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printTable(array $header, array $content);

    /**
     * @param string $filePath
     */
    public function printImage(string $filePath);
}

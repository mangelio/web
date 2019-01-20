<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout\Base;

use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentTransactionInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\Supporting\PrintBuffer;
use App\Service\Report\Pdf\Layout\Supporting\PrintTransaction;

abstract class BaseColumnedLayout
{
    /**
     * @var PdfDocumentTransactionInterface
     */
    private $pdfDocument;

    /**
     * @var int
     */
    private $columnCount;

    /**
     * @var float
     */
    private $totalWidth;

    /**
     * @var float[]
     */
    private $columnWidths;

    /**
     * @var float
     */
    private $columnGutter;

    /**
     * @var Cursor[]
     */
    private $columnCursors;

    /**
     * @var PrintBuffer
     */
    private $printBuffer;

    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $columnGutter
     * @param float $totalWidth
     * @param float[] $widths
     */
    protected function __construct(PdfDocumentInterface $pdfDocument, float $columnGutter, float $totalWidth, array $widths)
    {
        $this->pdfDocument = $pdfDocument;
        $this->columnCount = \count($widths);
        $this->columnGutter = $columnGutter;
        $this->totalWidth = $totalWidth;
        $this->columnWidths = $widths;

        $cursor = $pdfDocument->getCursor();
        $nextXStart = $cursor->getXCoordinate();
        $currentColumn = 0;
        do {
            $this->columnCursors[$currentColumn] = $cursor->setX($nextXStart);
            $nextXStart += $this->columnWidths[$currentColumn] + $this->columnGutter;
        } while (++$currentColumn < $this->columnCount);

        $this->printBuffer = new PrintBuffer($this->pdfDocument, $totalWidth);
    }

    /**
     * will end the columned layout.
     */
    public function getTransaction()
    {
        $transaction = new PrintTransaction($this->pdfDocument, $this->totalWidth, $this->printBuffer->flushBufferClosure());

        $transaction->setOnPostCommit(function () {
            // go to lowest column after printing stopped
            $lowestCursor = $this->columnCursors[0];
            for ($i = 1; $i < $this->columnCount; ++$i) {
                $other = $this->columnCursors[$i];
                if ($other->isLowerOnPageThan($lowestCursor)) {
                    $lowestCursor = $other;
                }
            }

            $this->pdfDocument->setCursor($lowestCursor->setX($this->columnCursors[0]->getXCoordinate()));
        });

        return $transaction;
    }

    /**
     * @return PrintBuffer
     */
    protected function getPrintBuffer(): PrintBuffer
    {
        return $this->printBuffer;
    }

    /**
     * @return float[]
     */
    protected function getColumnWidths(): array
    {
        return $this->columnWidths;
    }

    /**
     * @return int
     */
    protected function getColumnCount(): int
    {
        return $this->columnCount;
    }

    /**
     * @return Cursor[]
     */
    protected function getColumnCursors(): array
    {
        return $this->columnCursors;
    }

    /**
     * ensures the next printed elements are printed in the specified column
     * will throw an exception if the column region does not exist.
     *
     * @param int $nextColumn
     *
     * @throws \Exception
     */
    protected function switchColumns(int $currentColumn, int $nextColumn)
    {
        // save current cursor
        $this->columnCursors[$currentColumn] = $this->pdfDocument->getCursor();

        // set new cursor
        $this->activeColumn = $nextColumn;
        $this->pdfDocument->setCursor($this->columnCursors[$nextColumn]);
    }
}

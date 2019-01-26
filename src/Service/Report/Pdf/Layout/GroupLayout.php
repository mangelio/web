<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout;

use App\Service\Report\Document\Interfaces\Layout\GroupLayoutInterface;
use App\Service\Report\Document\Transaction\TransactionInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentTransactionInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\Supporting\PrintBuffer;
use App\Service\Report\Pdf\Layout\Supporting\PrintTransaction;

class GroupLayout implements GroupLayoutInterface
{
    /**
     * @var PdfDocumentTransactionInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * @var PrintBuffer
     */
    private $printBuffer;

    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $width
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $width)
    {
        $this->pdfDocument = $pdfDocument;
        $this->width = $width;

        $this->printBuffer = new PrintBuffer($pdfDocument, $width);
    }

    /**
     * will end the columned layout.
     *
     * @return TransactionInterface
     */
    public function getTransaction()
    {
        return self::createTransaction($this->printBuffer, $this->pdfDocument, $this->width);
    }

    /**
     * register a callable which prints to the pdf document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * ensure the cursor is below the printed content after the callable is finished to not mess up the layout.
     *
     * @param callable $callable takes a PdfDocumentInterface as an argument
     */
    public function registerPrintable(callable $callable)
    {
        $this->printBuffer->addPrintable($callable);
    }

    /**
     * creates the transaction and implements the grouping functionality.
     *
     * @param PrintBuffer $printBuffer
     * @param PdfDocumentTransactionInterface $pdfDocument
     * @param float $width
     *
     * @return PrintTransaction
     */
    private static function createTransaction(PrintBuffer $printBuffer, PdfDocumentTransactionInterface $pdfDocument, float $width)
    {
        $printContent = $printBuffer->flushBufferClosure();
        $transaction = new PrintTransaction($pdfDocument, $width, $printContent);

        // ensure not bigger than current page
        [$start, $end] = $transaction->calculatePrintArea();
        if ($start->getPage() === $end->getPage()) {
            return $transaction;
        }

        // start new page to fulfill grouping requirement
        // clone the print buffer else unexpected behaviour if reusing the layout
        $printBuffer = PrintBuffer::createFromExisting($printBuffer);
        $printBuffer->prependPrintable(function (PdfDocumentInterface $pdfDocument) {
            $pdfDocument->startNewPage();
        });

        $printContent = $printBuffer->flushBufferClosure();

        return new PrintTransaction($pdfDocument, $width, $printContent);
    }
}

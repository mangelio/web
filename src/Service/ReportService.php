<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\DateTimeFormatter;
use App\Helper\IssueHelper;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\PdfDefinition;
use App\Service\Report\Report;
use App\Service\Report\ReportElements;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportService implements ReportServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var ImageServiceInterface
     */
    private $imageService;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $reportAssetDir;

    /**
     * ReportService constructor.
     */
    public function __construct(ImageServiceInterface $imageService, SerializerInterface $serializer, TranslatorInterface $translator, PathServiceInterface $pathService, string $reportAssetDir)
    {
        $this->imageService = $imageService;
        $this->serializer = $serializer;
        $this->translator = $translator;
        $this->pathService = $pathService;
        $this->reportAssetDir = $reportAssetDir;
    }

    public function generatePdfReport(Paginator $paginator, Filter $filter, ReportElements $reportElements, ?string $author = null): string
    {
        $constructionSite = $filter->getConstructionSite();
        $issues = iterator_to_array($paginator->getIterator());

        $this->setScriptRuntime(count($issues));

        $formattedDate = (new DateTime())->format(DateTimeFormatter::DATE_TIME_FORMAT);
        if (null === $author) {
            $footer = $this->translator->trans('generated', ['%date%' => $formattedDate], 'report');
        } else {
            $footer = $this->translator->trans('generated_with_author', ['%date%' => $formattedDate, '%name%' => $author], 'report');
        }

        //create folder
        $reportId = $formattedDate.'-'.uniqid();
        $generationTargetFolder = $this->pathService->getTransientFolderForReports($constructionSite).'/'.$reportId;
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        // initialize report
        $logoPath = $this->reportAssetDir.'/logo.png';
        $pdfDefinition = new PdfDefinition($constructionSite->getName(), $footer, $logoPath);
        $report = new Report($pdfDefinition, $this->reportAssetDir);

        $this->addIntroduction($report, $constructionSite, $paginator, count($issues), $filter, $reportElements);

        if (\count($issues) > 0) {
            $this->addIssueContent($filter, $reportElements, $issues, $report, $generationTargetFolder);
        }

        $targetFilepath = $generationTargetFolder.'pdf';
        $report->save($targetFilepath);

        return $targetFilepath;
    }

    /**
     * @param Issue[] $issues
     */
    private function addMap(Report $report, Map $map, array $issues, string $generationTargetFolder)
    {
        $targetPath = $generationTargetFolder.'/'.$map->getId().'.jpg';
        if (!$this->imageService->renderMapFileWithIssues($map->getFile(), $issues, $targetPath, ImageServiceInterface::SIZE_FULL)) {
            $targetPath = null;
        }

        $report->addMap($map->getName(), $map->getContext(), $targetPath);
    }

    /**
     * @param Issue[] $issues
     */
    private function addIssueImageGrid(Report $report, array $issues)
    {
        $columnCount = 4;

        $imageGrid = [];
        $currentRow = [];
        foreach ($issues as $issue) {
            $currentIssue = [];

            $imagePath = $this->imageService->resizeIssueImage($issue->getImage(), ImageServiceInterface::SIZE_PREVIEW);
            if (null === $imagePath) {
                continue;
            }

            $currentIssue['imagePath'] = $imagePath;
            $currentIssue['identification'] = $issue->getNumber();
            $currentRow[] = $currentIssue;

            //add row to grid if applicable
            if (\count($currentRow) === $columnCount) {
                $imageGrid[] = $currentRow;
                $currentRow = [];
            }
        }
        if (\count($currentRow) > 0) {
            $imageGrid[] = $currentRow;
        }

        $report->addImageGrid($imageGrid, $columnCount);
    }

    private function addIntroduction(Report $report, ConstructionSite $constructionSite, Paginator $paginator, int $issueCount, Filter $filter, ReportElements $reportElements)
    {
        $filterEntries = [];

        /*
         * intentionally ignoring isMarked as this is part of the application, not the report
         */

        //collect all set status
        if (null !== $filter->getState()) {
            $status = [];
            if ($filter->getState() & Issue::STATE_REGISTERED) {
                $status[] = $this->translator->trans('state_values.registered', [], 'entity_issue');
            }
            if ($filter->getState() & Issue::STATE_SEEN) {
                $status[] = $this->translator->trans('state_values.seen', [], 'entity_issue');
            }
            if ($filter->getState() & Issue::STATE_RESPONDED) {
                $status[] = $this->translator->trans('state_values.responded', [], 'entity_issue');
            }
            if ($filter->getState() & Issue::STATE_REVIEWED) {
                $status[] = $this->translator->trans('state_values.reviewed', [], 'entity_issue');
            }

            $key = $this->translator->trans('status', [], 'entity_issue');
            if (4 === \count($status)) {
                $allStatus = $this->translator->trans('state_values.all', [], 'entity_issue');
                $filterEntries[$key] = $allStatus;
            } else {
                $or = $this->translator->trans('introduction.filter.or', [], 'report');
                $filterEntries[$key] = implode(' '.$or.' ', $status);
            }
        }

        //add craftsmen
        if (null !== $filter->getCraftsmanIds()) {
            $entities = $this->doctrine->getRepository(Craftsman::class)->findBy(['id' => $filter->getCraftsmanIds()]);
            $names = array_map(function (Craftsman $craftsman) { $craftsman->getName(); }, $entities);
            $craftsmen = $this->translator->trans('introduction.filter.craftsmen', ['%count%' => \count($names)], 'report');
            $filterEntries[$craftsmen] = implode(', ', $names);
        }

        //add trades
        if (null !== $filter->getCraftsmanTrades()) {
            $trades = $this->translator->trans('introduction.filter.trades', ['%count%' => \count($filter->getCraftsmanTrades())], 'report');
            $filterEntries[$trades] = implode(', ', $filter->getCraftsmanTrades());
        }

        //add maps
        if (null !== $filter->getMapIds()) {
            $entities = $this->doctrine->getRepository(Map::class)->findBy(['id' => $filter->getMapIds()]);
            $names = array_map(function (Map $map) { $map->getContext(); }, $entities);
            $maps = $this->translator->trans('introduction.filter.maps', ['%count%' => \count($names)], 'report');
            $filterEntries[$maps] = implode(', ', $names);
        }

        //add limit
        $responseLimit = $this->translator->trans('deadline', [], 'entity_issue');
        $filterEntries[$responseLimit] = $this->tryGetDateString($filter->getDeadlineAtBefore(), $filter->getDeadlineAtAfter());

        $registeredAt = $this->translator->trans('registered_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getRegisteredAtBefore(), $filter->getRegisteredAtAfter());

        $registeredAt = $this->translator->trans('responded_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getRespondedAtBefore(), $filter->getRespondedAtAfter());

        $registeredAt = $this->translator->trans('reviewed_at', [], 'trait_issue_status');
        $filterEntries[$registeredAt] = $this->tryGetDateString($filter->getReviewedAtBefore(), $filter->getReviewedAtAfter());

        // clear empty entries
        $filterEntries = array_filter($filterEntries);

        // add paginator info
        $totalItems = (int) $paginator->getTotalItems();
        $paginatorString = null;
        if ($totalItems !== $issueCount) {
            $start = (int) $paginator->getCurrentPage() * $totalItems;
            $end = $start + $issueCount;

            $paginatorString = $this->translator->trans('introduction.paginator', ['%start%' => $start, '%end%' => $end, '%total%' => $totalItems], 'report');
        }

        //add list of elements which are part of this report
        $elements = [];
        if ($reportElements->getTableByCraftsman()) {
            $elements[] = $this->translator->trans('table.by_craftsman', [], 'report');
        }
        if ($reportElements->getTableByMap()) {
            $elements[] = $this->translator->trans('table.by_map', [], 'report');
        }
        if ($reportElements->getTableByTrade()) {
            $elements[] = $this->translator->trans('table.by_trade', [], 'report');
        }
        $elements[] = $this->translator->trans('issues.detailed', [], 'report');
        if ($reportElements->getWithImages()) {
            $elements[\count($elements) - 1] .= ' '.$this->translator->trans('issues.with_images', [], 'report');
        }
        $reportElements = implode(', ', $elements);

        $addressLines = implode("\n", $constructionSite->getAddressLines());

        $report->addIntroduction(
            $this->imageService->resizeConstructionSiteImage($constructionSite->getImage(), ImageServiceInterface::SIZE_PREVIEW),
            $constructionSite->getName(),
            $addressLines,
            $reportElements,
            $filterEntries,
            $paginatorString,
            $this->translator->trans('entity.name', [], 'entity_filter')
        );
    }

    private function tryGetDateString(?\DateTime $before, ?\DateTime $after): ?string
    {
        $beforeString = null !== $before ? $before->format(DateTimeFormatter::DATE_FORMAT) : null;
        $afterString = null !== $after ? $after->format(DateTimeFormatter::DATE_FORMAT) : null;

        if (null !== $before && null !== $after) {
            return $afterString.' - '.$beforeString;
        }

        if (null !== $before) {
            return$this->translator->trans('introduction.filter.earlier_than', ['%date%' => $beforeString], 'report');
        }

        if (null !== $after) {
            return$this->translator->trans('introduction.filter.later_than', ['%date%' => $afterString], 'report');
        }

        return null;
    }

    /**
     * @param Issue[] $issues
     */
    private function addIssueTable(Report $report, Filter $filter, array $issues)
    {
        $showRegistered = null === $filter->getRegisteredAtBefore() || $filter->getRegisteredAtAfter();
        $showResponded = null === $filter->getRespondedAtBefore() || $filter->getRespondedAtAfter();
        $showReviewed = null === $filter->getReviewedAtBefore() || $filter->getReviewedAtAfter();

        $tableHeader[] = '#';
        $tableHeader[] = $this->translator->trans('entity.name', [], 'entity_craftsman');
        $tableHeader[] = $this->translator->trans('description', [], 'entity_issue');
        $tableHeader[] = $this->translator->trans('deadline', [], 'entity_issue');

        if ($showRegistered) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('state_values.registered', [], 'entity_issue')], 'report');
        }

        if ($showResponded) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('state_values.responded', [], 'entity_issue')], 'report');
        }

        if ($showReviewed) {
            $tableHeader[] = $this->translator->trans('table.in_state_since', ['%status%' => $this->translator->trans('state_values.reviewed', [], 'entity_issue')], 'report');
        }

        $tableContent = [];
        foreach ($issues as $issue) {
            $row = [];
            $row[] = $issue->getNumber();
            $row[] = $issue->getCraftsman()->getCompany()."\n".$issue->getCraftsman()->getTrade();
            $row[] = $issue->getDescription();
            $row[] = (null !== $issue->getDeadline()) ? $issue->getDeadline()->format(DateTimeFormatter::DATE_FORMAT) : '';

            if ($showRegistered) {
                $row[] = null !== $issue->getRegisteredAt() ? $issue->getRegisteredAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getRegistrationBy()->getName() : '';
            }

            if ($showResponded) {
                $row[] = null !== $issue->getRespondedAt() ? $issue->getRespondedAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getResponseBy()->getName() : '';
            }

            if ($showReviewed) {
                $row[] = null !== $issue->getReviewedAt() ? $issue->getReviewedAt()->format(DateTimeFormatter::DATE_FORMAT)."\n".$issue->getReviewBy()->getName() : '';
            }

            $tableContent[] = $row;
        }

        $report->addTable($tableHeader, $tableContent, null, 10);
    }

    /**
     * @param Issue[][] $issuesPerMap
     * @param $tableContent
     * @param $tableHeader
     */
    private function addAggregatedIssuesInfo(Filter $filter, array $orderedMaps, array $issuesPerMap, array &$tableContent, array &$tableHeader)
    {
        //count issue status per map
        $countsPerElement = [];
        foreach ($orderedMaps as $index => $element) {
            $countPerMap = [0, 0, 0];
            foreach ($issuesPerMap[$index] as $issue) {
                if ($issue->getReviewedAt() >= Issue::STATE_REVIEWED) {
                    ++$countPerMap[2];
                } elseif ($issue->getRespondedAt() >= Issue::STATE_RESPONDED) {
                    ++$countPerMap[1];
                } else {
                    ++$countPerMap[0];
                }
            }
            $countsPerElement[$index] = $countPerMap;
        }

        $tableHeader[] = $this->translator->trans('state_values.registered', [], 'entity_issue');
        foreach ($countsPerElement as $elementId => $count) {
            $tableContent[$elementId][] = $count[0];
        }

        $tableHeader[] = $this->translator->trans('state_values.responded', [], 'entity_issue');
        foreach ($countsPerElement as $elementId => $count) {
            $tableContent[$elementId][] = $count[1];
        }

        $tableHeader[] = $this->translator->trans('state_values.reviewed', [], 'entity_issue');
        foreach ($countsPerElement as $elementId => $count) {
            $tableContent[$elementId][] = $count[2];
        }
    }

    /**
     * @param Issue[] $issues
     */
    private function addTableByMap(Report $report, Filter $filter, array $issues)
    {
        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);

        //prepare header & content with specific content
        $tableHeader = [$this->translator->trans('context', [], 'entity_map'), $this->translator->trans('entity.name', [], 'entity_map')];

        //add map name & map context to table
        $tableContent = [];
        foreach ($orderedMaps as $mapId => $map) {
            $tableContent[$mapId] = [$map->getContext(), $map->getName()];
        }

        //add accumulated info
        $this->addAggregatedIssuesInfo($filter, $orderedMaps, $issuesPerMap, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_map', [], 'report'));
    }

    /**
     * @param Issue[] $issues
     */
    private function addTableByCraftsman(Report $report, Filter $filter, array $issues)
    {
        /* @var Craftsman[] $orderedCraftsman */
        /* @var Issue[][] $issuesPerCraftsman */
        IssueHelper::issuesToOrderedCraftsman($issues, $orderedCraftsman, $issuesPerCraftsman);

        //prepare header & content with specific content
        $tableHeader = [$this->translator->trans('entity.name', [], 'entity_craftsman')];

        //add map name & map context to table
        $tableContent = [];
        foreach ($orderedCraftsman as $craftsmanId => $craftsman) {
            $tableContent[$craftsmanId] = [$craftsman->getName()];
        }

        //add accumulated info
        $this->addAggregatedIssuesInfo($filter, $orderedCraftsman, $issuesPerCraftsman, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_craftsman', [], 'report'));
    }

    /**
     * @param Issue[] $issues
     */
    private function addTableByTrade(Report $report, Filter $filter, array $issues)
    {
        /* @var string[] $orderedTrade */
        /* @var Issue[][] $issuesPerTrade */
        IssueHelper::issuesToOrderedTrade($issues, $orderedTrade, $issuesPerTrade);

        //prepare header & content with specific content
        $tableHeader = [$this->translator->trans('trade', [], 'entity_craftsman')];

        //add map name & map context to table
        $tableContent = [];
        foreach ($orderedTrade as $trade) {
            $tableContent[$trade] = [$trade];
        }

        //add accumulated info
        $this->addAggregatedIssuesInfo($filter, $orderedTrade, $issuesPerTrade, $tableContent, $tableHeader);

        //write to pdf
        $report->addTable($tableHeader, $tableContent, $this->translator->trans('table.by_trade', [], 'report'));
    }

    private function addIssueContent(Filter $filter, ReportElements $reportElements, array $issues, Report $report, string $generationTargetFolder): void
    {
        // add tables
        if ($reportElements->getTableByCraftsman()) {
            $this->addTableByCraftsman($report, $filter, $issues);
        }
        if ($reportElements->getTableByMap()) {
            $this->addTableByMap($report, $filter, $issues);
        }
        if ($reportElements->getTableByTrade()) {
            $this->addTableByTrade($report, $filter, $issues);
        }

        /* @var Map[] $orderedMaps */
        /* @var Issue[][] $issuesPerMap */
        IssueHelper::issuesToOrderedMaps($issues, $orderedMaps, $issuesPerMap);
        foreach ($orderedMaps as $map) {
            $issues = $issuesPerMap[$map->getId()];
            $this->addMap($report, $map, $issues, $generationTargetFolder);
            $this->addIssueTable($report, $filter, $issues);
            if ($reportElements->getWithImages()) {
                $this->addIssueImageGrid($report, $issues);
            }
        }
    }

    /**
     * @param \Traversable $numberOfIssues
     */
    private function setScriptRuntime(int $numberOfIssues): void
    {
        // 0.5s per issue seems reasonable
        $maxExecutionTime = max(120, $numberOfIssues / 2);
        $executionTime = max(ini_get('max_execution_time'), $maxExecutionTime);
        ini_set('max_execution_time', $executionTime);

        // 500 kb per issue seems reasonable
        $memoryLimitMbs = max(256, 0.5 * $numberOfIssues);
        ini_set('memory_limit', $memoryLimitMbs.'M');
    }
}

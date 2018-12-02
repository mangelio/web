<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Image;

use App\Controller\Base\BaseDoctrineController;
use App\Controller\External\Traits\CraftsmanAuthenticationTrait;
use App\Controller\Traits\ImageDownloadTrait;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/c/{identifier}")
 */
class CraftsmanController extends BaseDoctrineController
{
    use ImageDownloadTrait;
    use CraftsmanAuthenticationTrait;

    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + [ImageServiceInterface::class => ImageServiceInterface::class];
    }

    /**
     * @Route("/map/{map}/{file}/{hash}/{size}", name="external_image_craftsman_map")
     *
     * @param $identifier
     * @param Map $map
     * @param MapFile $file
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function mapAction($identifier, Map $map, MapFile $file, $size, ImageServiceInterface $imageService)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman)) {
            throw new NotFoundHttpException();
        }

        if ($map->getFile() !== $file) {
            throw new NotFoundHttpException();
        }

        //get issues to put on map
        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setMaps([$map->getId()]);
        $filter->setRespondedStatus(false);
        $filter->setRegistrationStatus(true);
        $filter->setReviewedStatus(false);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        //generate map & print
        $imagePath = $imageService->generateMapImage($map, $issues, $imageService->ensureValidSize($size));

        return $this->file($imagePath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/issue/{issue}/{image}/{size}", name="external_image_craftsman_issue")
     *
     * @param $identifier
     * @param Issue $issue
     * @param IssueImage $image
     * @param $size
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function issueAction($identifier, Issue $issue, IssueImage $image, $size, ImageServiceInterface $imageService)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman)) {
            throw new NotFoundHttpException();
        }

        return $this->file($this->getImagePathForIssue($issue, $image, $size, $imageService), $image->getFilename(), ResponseHeaderBag::DISPOSITION_INLINE);
    }
}

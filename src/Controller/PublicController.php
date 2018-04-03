<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use App\Controller\Base\BaseDoctrineController;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\Marker;
use App\Model\BuildingMap\BuildingMapMarkerInfo;
use Intervention\Image\ImageManager;
use Psr\Http\Message\ResponseInterface;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/public")
 *
 * @return Response
 */
class PublicController extends BaseDoctrineController
{
    /**
     * @Route("/render/{marker}", name="public_render")
     * @param Marker $marker
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws PdfDoesNotExist
     */
    public function renderAction(Marker $marker)
    {
        $mapFileName = __DIR__ . "/../../public/upload/" . $marker->getBuildingMap()->getFileName();
        $imageFileName = $mapFileName . ".jpg";
        if (!file_exists($imageFileName)) {
            $pdf = new Pdf($mapFileName);
            $pdf->saveImage($imageFileName);
        }

        $renderFilename = $mapFileName . "_" .
            $marker->getFrameYLength() . $marker->getFrameXHeight() . $marker->getFrameYPercentage() . $marker->getFrameXPercentage() .
            $marker->getMarkXPercentage() . $marker->getMarkYPercentage() .
            ".jpg";

        if (!file_exists($renderFilename)) {
            $manager = new ImageManager(array('driver' => 'imagick'));
            $image = $manager->make($imageFileName);

            $width = $image->getWidth();
            $height = $image->getHeight();

            $newWidth = $width * $marker->getFrameYLength();
            $newHeight = $height * $marker->getFrameXHeight();
            $image = $image->crop(
                (int)($newWidth),
                (int)($newHeight),
                (int)($width * $marker->getFrameXPercentage()),
                (int)($height * $marker->getFrameYPercentage())
            );


            $xPos = $newWidth * $marker->getMarkXPercentage();
            $yPos = $newHeight * $marker->getMarkYPercentage();

            $total = $newWidth + $newHeight;

            $sensibleNumber = function ($relative) use ($total) {
                return (int)($total / 200 * $relative);
            };

            $color = "#403075";
            $image = $image->circle($sensibleNumber(1), $xPos, $yPos, function ($draw) use ($color) {
                $draw->background($color);
            });

            $image = $image->circle($sensibleNumber(6), $xPos, $yPos, function ($draw) use ($color, $sensibleNumber) {
                $draw->border($sensibleNumber(0.6), $color);
            });


            $image->save($renderFilename);
        }

        return $this->file($renderFilename);
    }

    /**
     * @Route("/{guid}", name="public_view")
     * @param $guid
     * @return Response
     */
    public function viewAction($guid)
    {
        $markers = $this->getMarkers($guid);
        return $this->viewMarkers($markers);
    }


    /**
     * @Route("/{guid}/print", name="public_view_print")
     * @param $guid
     * @return Response
     */
    public function viewPrintAction($guid)
    {
        $markers = $this->getMarkers($guid);
        return $this->printMarkers($markers);
    }

    /**
     * @Route("/{guid}/{guid2}", name="public_view_2")
     * @param $guid
     * @param $guid2
     * @return Response
     */
    public function viewDoubleAction($guid, $guid2)
    {
        $markers = $this->getMarkersForDouble($guid, $guid2);
        return $this->viewMarkers($markers);
    }

    /**
     * @Route("/{guid}/{guid2}/print", name="public_view_2_print")
     * @param $guid
     * @param $guid2
     * @return Response
     */
    public function viewDoublePrintAction($guid, $guid2)
    {
        $markers = $this->getMarkersForDouble($guid, $guid2);
        return $this->printMarkers($markers);
    }

    /**
     * @param $guid
     * @param $guid2
     * @return array|Response
     */
    private function getMarkersForDouble($guid, $guid2)
    {
        //guid from building, guid2 from craftsman
        $building = $this->getDoctrine()->getRepository(Building::class)->findOneBy(["publicIdentifier" => $guid]);
        if ($building == null || !$building->isAccessible()) {
            return $this->notAccessibleError();
        }
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(["id" => $guid2]);
        if ($craftsman == null) {
            return $this->notAccessibleError();
        }
        $markers = [];
        foreach ($building->getMarkers() as $marker) {
            if ($craftsman->getId() == $marker->getCraftsman()->getId()) {
                $markers[] = $marker;
            }
        }
        return $markers;
    }

    /**
     * @param $guid
     * @return Marker[]|Response
     */
    private function getMarkers($guid)
    {

        //probably a map
        $map = $this->getDoctrine()->getRepository(BuildingMap::class)->findOneBy(["publicIdentifier" => $guid]);
        if ($map == null || !$map->isAccessible()) {
            return $this->notAccessibleError();
        }

        /* @var Marker[] $markers */
        $markers = $map->getMarkers()->toArray();
        return $markers;
    }

    /**
     * @param Marker[]|Response $markers
     * @return Response
     */
    private function viewMarkers($markers)
    {
        if ($markers instanceof Response) {
            return $markers;
        }

        /* @var Marker[] $pendingMarkers */
        $pendingMarkers = [];
        /* @var Marker[] $approvedMarkers */
        $approvedMarkers = [];
        foreach ($markers as $marker) {
            if ($marker->getApproved()) {
                $approvedMarkers[] = $marker;
            } else {
                $pendingMarkers[] = $marker;
            }
        }

        return $this->render("public/view.html.twig", ["pending_markers" => $pendingMarkers, "approved_markers" => $approvedMarkers]);
    }

    /**
     * @param Marker[]|Response $markers
     * @return Response
     */
    private function printMarkers($markers)
    {
        if ($markers instanceof Response) {
            return $markers;
        }

        /* @var BuildingMapMarkerInfo[] $mapMarkerInfo */
        $mapMarkerInfo = [];
        foreach ($markers as $marker) {
            if (!isset($mapMarkerInfo[$marker->getId()])) {
                $new = new BuildingMapMarkerInfo();
                $new->setBuildingMap($marker->getBuildingMap());
                $mapMarkerInfo[$marker->getId()] = $new;
            }
            $info = $mapMarkerInfo[$marker->getId()];
            $info->addMarker($marker);
        }

        return $this->render("public/print.html.twig", ["map_info" => $mapMarkerInfo]);
    }

    private function notAccessibleError()
    {
        return $this->render("public/not_accessible.html.twig");
    }
}

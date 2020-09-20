<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api;

use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\CraftsmenRequest;
use App\Enum\ApiStatus;
use App\Tests\Controller\Api\Base\ApiController;
use function is_array;

class DispatchControllerTest extends ApiController
{
    public function testCraftsmanList()
    {
        $url = '/api/dispatch/craftsman/list';

        $constructionSite = $this->getSomeConstructionSite();
        $constructionSiteRequest = new ConstructionSiteRequest();
        $constructionSiteRequest->setConstructionSiteId($constructionSite->getId());

        $response = $this->authenticatedPostRequest($url, $constructionSiteRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmanData->data);
        $this->assertNotNull($craftsmanData->data->craftsmen);

        $this->assertTrue(is_array($craftsmanData->data->craftsmen));
        foreach ($craftsmanData->data->craftsmen as $craftsman) {
            $this->assertNotNull($craftsman);
            $this->assertObjectHasAttribute('name', $craftsman);
            $this->assertObjectHasAttribute('trade', $craftsman);
            $this->assertObjectHasAttribute('notReadIssuesCount', $craftsman);
            $this->assertObjectHasAttribute('notRespondedIssuesCount', $craftsman);
            $this->assertObjectHasAttribute('nextResponseLimit', $craftsman);
            $this->assertObjectHasAttribute('lastEmailSent', $craftsman);
            $this->assertObjectHasAttribute('lastOnlineVisit', $craftsman);
            $this->assertObjectHasAttribute('personalUrl', $craftsman);
        }
    }

    public function testDispatch()
    {
        $url = '/api/dispatch';

        $constructionSite = $this->getSomeConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];
        $dispatchRequest = new CraftsmenRequest();
        $dispatchRequest->setConstructionSiteId($constructionSite->getId());
        $dispatchRequest->setCraftsmanIds([$craftsman->getId()]);

        $response = $this->authenticatedPostRequest($url, $dispatchRequest);
        $craftsmanData = $this->checkResponse($response, ApiStatus::SUCCESS);

        $this->assertNotNull($craftsmanData->data);
        $this->assertObjectHasAttribute('successfulIds', $craftsmanData->data);
        $this->assertObjectHasAttribute('skippedIds', $craftsmanData->data);
        $this->assertObjectHasAttribute('failedIds', $craftsmanData->data);
    }
}
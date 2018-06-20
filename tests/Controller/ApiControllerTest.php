<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:57 AM
 */

namespace App\Tests\Controller;

use App\Api\Entity\Issue;
use App\Api\Entity\IssuePosition;
use App\Api\Entity\IssueStatus;
use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Api\Request\ReadRequest;
use App\Api\Response\Base\AbstractResponse;
use App\Api\Response\ErrorResponse;
use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\ApiController;
use App\Enum\ApiStatus;
use App\Tests\Controller\Base\FixturesTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class ApiControllerTest extends FixturesTestCase
{
    /**
     * tests the login functionality
     */
    public function testLogin()
    {
        $client = static::createClient();
        $doRequest = function ($username, $password) use ($client) {
            $client->request(
                'POST',
                '/api/login',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                '{"username":"' . $username . '", "passwordHash":"' . hash("sha256", $password) . '"}'
            );

            return $client->getResponse();
        };


        $response = $doRequest("unknwon", "ad");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::UNKNOWN_USERNAME);

        $response = $doRequest("f@mangel.io", "ad");
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::WRONG_PASSWORD);

        $response = $doRequest("f@mangel.io", "asdf");
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        $this->assertNotNull($loginResponse->data);
        $this->assertNotNull($loginResponse->data->user);
        $this->assertNotNull($loginResponse->data->user->givenName);
        $this->assertNotNull($loginResponse->data->user->familyName);
        $this->assertNotNull($loginResponse->data->user->authenticationToken);
        $this->assertNotNull($loginResponse->data->user->meta->id);
        $this->assertNotNull($loginResponse->data->user->meta->lastChangeTime);
    }

    /**
     * @param Response $response
     * @param $apiStatus
     * @param string $message
     * @return mixed|null
     */
    private function checkResponse(Response $response, $apiStatus, $message = "")
    {
        if ($apiStatus == ApiStatus::SUCCESSFUL) {
            $successful = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $successful->status, $response->getContent());
            $this->assertEquals(200, $response->getStatusCode());
            return $successful;
        } else if ($apiStatus == ApiStatus::FAIL) {
            $failed = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $failed->status, $response->getContent());
            $this->assertEquals($message, $failed->message);
            $this->assertEquals(200, $response->getStatusCode());
            return $failed;
        } else if ($apiStatus == ApiStatus::ERROR) {
            $error = json_decode($response->getContent());
            $this->assertEquals($apiStatus, $error->status);
            $this->assertEquals($message, $error->message);
            $this->assertNotEquals(200, $response->getStatusCode());
            return $error;
        }
        return null;
    }

    /**
     * gets an authenticated user
     *
     * @param Client $client
     * @return \stdClass
     */
    private function getAuthenticatedUser(Client $client)
    {
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            '{"username":"f@mangel.io", "passwordHash":"' . hash("sha256", "asdf") . '"}'
        );

        $json = $client->getResponse()->getContent();
        $response = json_decode($json);
        return $response->data->user;
    }

    /**
     * get the state of the server
     *
     * @param Client $client
     * @param $authenticatedUser
     * @return mixed|null
     */
    private function getServerEntities(Client $client, $authenticatedUser)
    {
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (ReadRequest $readRequest) use ($client, $serializer) {
            $json = $serializer->serialize($readRequest, "json");
            $client->request(
                'POST',
                '/api/read',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        # update all
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);

        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime((new \DateTime())->setTimestamp(0)->format("c"));
        $readRequest->setUser($userMeta);

        $readRequest->setBuildings([]);
        $readRequest->setCraftsmen([]);
        $readRequest->setIssues([]);
        $readRequest->setMaps([]);

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        $this->assertNotNull($readResponse->data);
        $this->assertNotNull($readResponse->data->user);
        $this->assertNotNull($readResponse->data->changedBuildings);
        $this->assertTrue(count($readResponse->data->changedBuildings) > 0);
        $this->assertTrue(count($readResponse->data->changedCraftsmen) > 0);
        $this->assertTrue(count($readResponse->data->changedMaps) > 0);
        $this->assertTrue(count($readResponse->data->changedIssues) > 0);

        $serverData = new \stdClass();
        $serverData->buildings = $readResponse->data->changedBuildings;
        $serverData->craftsmen = $readResponse->data->changedCraftsmen;
        $serverData->maps = $readResponse->data->changedMaps;
        $serverData->issues = $readResponse->data->changedIssues;

        return $serverData;
    }

    /**
     * tests the create issue method
     */
    public function testRead()
    {
        $client = static::createClient();
        $authenticatedUser = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (ReadRequest $readRequest) use ($client, $serializer) {
            $json = $serializer->serialize($readRequest, "json");
            $client->request(
                'POST',
                '/api/read',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };
        $serverData = $this->getServerEntities($client, $authenticatedUser);

        ### update none
        $readRequest = new ReadRequest();
        $readRequest->setAuthenticationToken($authenticatedUser->authenticationToken);
        $userMeta = new ObjectMeta();
        $userMeta->setId($authenticatedUser->meta->id);
        $userMeta->setLastChangeTime($authenticatedUser->meta->lastChangeTime);
        $readRequest->setUser($userMeta);

        //transform objects to meta object
        $getMetas = function ($entities, $invalids = 1, $old = 0, $lost = 0) {
            //convert to object meta
            $metas = [];
            foreach ($entities as $entity) {
                if ($lost-- > 0) {
                    //skip to lose meta
                    continue;
                }
                $meta = new ObjectMeta();
                $meta->setId($entity->meta->id);
                if ($old-- > 0) {
                    //set to min datetime to force update
                    $meta->setLastChangeTime(((new \DateTime())->setTimestamp(0)->format("c")));
                } else {
                    $meta->setLastChangeTime($entity->meta->lastChangeTime);
                }
                $metas[] = $meta;
            }

            for ($i = 0; $i < $invalids; $i++) {
                //create invalid & add
                $meta = new ObjectMeta();
                $meta->setId(Uuid::uuid4());
                $meta->setLastChangeTime((new \DateTime())->setTimestamp(0)->format("c"));
                $metas[] = $meta;
            }

            return $metas;
        };

        //set them in the request
        $readRequest->setBuildings($getMetas($serverData->buildings));
        $readRequest->setCraftsmen($getMetas($serverData->craftsmen));
        $readRequest->setMaps($getMetas($serverData->maps));
        $readRequest->setIssues($getMetas($serverData->issues));

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->user);
        $this->assertEmpty($readResponse->data->changedBuildings);
        $this->assertEmpty($readResponse->data->changedCraftsmen);
        $this->assertEmpty($readResponse->data->changedMaps);
        $this->assertEmpty($readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedBuildingIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);

        ### update, remove & add at the same time
        //set them in the request
        $readRequest->setBuildings($getMetas($serverData->buildings, 1, 1, 1));
        $readRequest->setCraftsmen($getMetas($serverData->craftsmen, 1, 1, 1));
        $readRequest->setMaps($getMetas($serverData->maps, 1, 1, 1));
        $readRequest->setIssues($getMetas($serverData->issues, 1, 1, 1));

        $response = $doRequest($readRequest);
        $readResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        $this->assertNotNull($readResponse->data);
        $this->assertNull($readResponse->data->user);
        $this->assertCount(2, $readResponse->data->changedBuildings);
        $this->assertCount(2, $readResponse->data->changedCraftsmen);
        $this->assertCount(2, $readResponse->data->changedMaps);
        $this->assertCount(2, $readResponse->data->changedIssues);
        $this->assertCount(1, $readResponse->data->removedBuildingIDs);
        $this->assertCount(1, $readResponse->data->removedCraftsmanIDs);
        $this->assertCount(1, $readResponse->data->removedMapIDs);
        $this->assertCount(1, $readResponse->data->removedIssueIDs);
    }

    /**
     * tests the create issue method
     */
    public function testCreateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, "json") . '}';
            $client->request(
                'POST',
                '/api/issue/create',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        $imageFilename = $this->getNewGuid() . ".jpg";

        $issue = new Issue();
        $issue->setWasAddedWithClient(true);
        $issue->setIsMarked(true);
        $issue->setImageFilename($imageFilename);
        $issue->setDescription("description");
        $issue->setMap($serverData->maps[0]->meta->id);

        $issue->setStatus(new IssueStatus());

        $meta = new ObjectMeta();
        $meta->setId($this->getNewGuid());
        $meta->setLastChangeTime((new \DateTime())->format("c"));
        $issue->setMeta($meta);

        $issuePosition = new IssuePosition();
        $issuePosition->setX(0.4);
        $issuePosition->setY(0.3);
        $issuePosition->setZoomScale(0.5);
        $issue->setPosition($issuePosition);

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::GUID_ALREADY_IN_USE);

        //check issue without position
        $issue->setPosition(null);
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);
        $this->verifyIssue($issueResponse->data->issue, $issue);
    }

    /**
     * checks if the issue is of the expected form
     *
     * @param $checkIssue
     * @param Issue $issue
     */
    private function verifyIssue($checkIssue, Issue $issue)
    {
        //check properties
        $this->assertEquals($checkIssue->wasAddedWithClient, $issue->getWasAddedWithClient());
        $this->assertEquals($checkIssue->isMarked, $issue->getIsMarked());
        $this->assertEquals($checkIssue->imageFilename, $issue->getImageFilename());
        $this->assertEquals($checkIssue->description, $issue->getDescription());
        $this->assertEquals($checkIssue->map, $issue->getMap());

        //check meta is newer/equal & id is preserved
        $this->assertEquals($checkIssue->meta->id, $issue->getMeta()->getId());
        $this->assertTrue($checkIssue->meta->lastChangeTime >= $issue->getMeta()->getLastChangeTime());

        //check position transferred correctly
        if ($issue->getPosition() != null) {
            $this->assertNotNull($checkIssue->position);
            $this->assertEquals($checkIssue->position->x, $issue->getPosition()->getX());
            $this->assertEquals($checkIssue->position->y, $issue->getPosition()->getY());
            $this->assertEquals($checkIssue->position->zoomScale, $issue->getPosition()->getZoomScale());
        } else {
            $this->assertNull($checkIssue->position);
        }

        //check status
        $this->assertNotNull($checkIssue->status);
    }

    /**
     * tests the create issue method
     */
    public function testUpdateIssue()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (Issue $issue) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, "json") . '}';
            $client->request(
                'POST',
                '/api/issue/update',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);

        $imageFilename = $this->getNewGuid() . ".jpg";

        /** @var Issue $issue */
        $issue = $serializer->deserialize(json_encode($serverData->issues[0]), Issue::class, "json");
        $issue->setWasAddedWithClient(false);
        $issue->setIsMarked(false);
        $issue->setImageFilename($imageFilename);
        $issue->setDescription("description 2");
        $issue->setMap($serverData->maps[0]->meta->id);

        $issue->setStatus(new IssueStatus());

        $issuePosition = new IssuePosition();
        $issuePosition->setX(0.4);
        $issuePosition->setY(0.3);
        $issuePosition->setZoomScale(0.5);
        $issue->setPosition($issuePosition);

        $response = $doRequest($issue);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        //check response has issue
        $this->assertNotNull($issueResponse->data);
        $this->assertNotNull($issueResponse->data->issue);
        $checkIssue = $issueResponse->data->issue;
        //fully check issue
        $this->verifyIssue($checkIssue, $issue);

        //check with non-existing
        $issue->getMeta()->setId($this->getNewGuid());
        $response = $doRequest($issue);
        $this->checkResponse($response, ApiStatus::FAIL, ApiController::GUID_NOT_FOUND);
    }

    private function getNewGuid()
    {
        return strtoupper(Uuid::uuid4()->toString());
    }


    /**
     * tests upload/download functionality
     */
    public function testFileUploadDownload()
    {
        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function ($issue, UploadedFile $file) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($issue, "json") . '}';
            $client->request(
                'POST',
                '/api/issue/update',
                [],
                ["some key" => $file],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $serverData = $this->getServerEntities($client, $user);
        $issue = $serverData->issues[0];

        $filePath = __DIR__ . "/../Files/sample.jpg";
        $copyPath = __DIR__ . "/../Files/sample_2.jpg";
        copy($filePath, $copyPath);

        $file = new UploadedFile(
            $copyPath,
            'upload.jpg',
            'image/jpeg'
        );
        $response = $doRequest($issue, $file);
        $issueResponse = $this->checkResponse($response, ApiStatus::SUCCESSFUL);

        //check response issue updated
        $this->verifyIssue($issueResponse->issue, $issue);


        $client = static::createClient();
        $user = $this->getAuthenticatedUser($client);
        $serializer = $client->getContainer()->get("serializer");
        $doRequest = function (ObjectMeta $objectMeta) use ($client, $user, $serializer) {
            $json = '{"authenticationToken":"' . $user->authenticationToken . '", "issue":' . $serializer->serialize($objectMeta, "json") . '}';
            $client->request(
                'POST',
                '/api/file/download',
                [],
                [],
                ["CONTENT_TYPE" => "application/json"],
                $json
            );

            return $client->getResponse();
        };

        $issueMeta = new ObjectMeta();
        $issueMeta->setId($issue->id);
        $issueMeta->setLastChangeTime($issue->lastChangeTime);
        $response = $doRequest($issueMeta);
        $this->assertInstanceOf(BinaryFileResponse::class, $response);

    }
}
<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ConstructionManager;
use App\Tests\DataFixtures\TestUserFixtures;
use App\Tests\Traits\AssertApiTrait;
use App\Tests\Traits\AuthenticationTrait;
use Doctrine\Persistence\ManagerRegistry;
use Liip\TestFixturesBundle\Test\FixturesTrait;

class ConstructionManagerTest extends ApiTestCase
{
    use FixturesTrait;
    use AssertApiTrait;
    use AuthenticationTrait;

    public function testInvalidMethods()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class]);
        $testUser = $this->loginApiTestUser($client);

        $this->assertApiOperationUnsupported($client, '/api/construction_managers', 'POST');
        $this->assertApiOperationUnsupported($client, '/api/construction_managers/'.$testUser->getId(), 'DELETE', 'PUT', 'PATCH');
    }

    public function testValidMethodsNeedAuthentication()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class]);

        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers', 'GET');

        $userRepository = static::$container->get(ManagerRegistry::class)->getRepository(ConstructionManager::class);
        $testUser = $userRepository->findOneByEmail(TestUserFixtures::TEST_EMAIL);
        $this->assertApiOperationNotAuthorized($client, '/api/construction_managers/'.$testUser->getId(), 'GET');
    }

    public function testGet()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestUserFixtures::class]);
        $this->loginApiTestUser($client);

        $response = $client->request('GET', '/api/construction_managers', [
            'headers' => ['Content-Type' => 'application/json'],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertContainsOnlyListedFields($response, 'givenName', 'familyName', 'email', 'phone');
    }
}
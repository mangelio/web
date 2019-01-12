<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Api\External;

use App\Enum\ApiStatus;
use App\Tests\Controller\Api\External\Base\ApiController;
use Symfony\Bundle\FrameworkBundle\Client;

class TrialControllerTest extends ApiController
{
    /**
     * tests the login functionality.
     */
    public function testTrial()
    {
        $client = static::createClient();

        $response = $this->doTrialRequest($client, null, null);
        $trialResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertNotNull($trialResponse->data);
        $this->assertNotNull($trialResponse->data->trialUser);
        $this->assertNotNull($trialResponse->data->trialUser->email);
        $this->assertNotNull($trialResponse->data->trialUser->plainPassword);

        $email = $trialResponse->data->trialUser->email;
        $this->assertTrue(\mb_strlen($email) > 8);
        // ensure email is of the form some_prefix@test.personalurl.ch
        $this->assertTrue(preg_match('/([a-z_]){5,}@test\..+/', $email) === 1, $email);

        $plainPassword = $trialResponse->data->trialUser->plainPassword;
        $this->assertTrue(\mb_strlen($plainPassword) > 8);

        $response = $this->doLoginRequest($client, $email, $plainPassword);
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertNotNull($loginResponse->data->user->givenName);
        $this->assertNotNull($loginResponse->data->user->familyName);
    }

    /**
     * tests the login functionality.
     */
    public function testRecommendationAccepted()
    {
        $givenName = 'Anna';
        $familyName = 'Schweigert';

        $client = static::createClient();

        $response = $this->doTrialRequest($client, $givenName, $familyName);
        $trialResponse = $this->checkResponse($response, ApiStatus::SUCCESS);

        $email = $trialResponse->data->trialUser->email;
        $plainPassword = $trialResponse->data->trialUser->plainPassword;

        $response = $this->doLoginRequest($client, $email, $plainPassword);
        $loginResponse = $this->checkResponse($response, ApiStatus::SUCCESS);
        $this->assertSame($givenName, $loginResponse->data->user->givenName);
        $this->assertNotNull($familyName, $loginResponse->data->user->familyName);
    }

    /**
     * @param Client $client
     * @param $username
     * @param $password
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function doLoginRequest(Client $client, $username, $password)
    {
        $client->request(
            'POST',
            '/api/external/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"username":"' . $username . '", "passwordHash":"' . hash('sha256', $password) . '"}'
        );

        return $client->getResponse();
    }

    /**
     * @param Client $client
     * @param string|null $givenName
     * @param string|null $familyName
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function doTrialRequest(Client $client, ?string $givenName, ?string $familyName)
    {
        $givenNamePayload = $givenName === null ? 'null' : '"' . $givenName . '"';
        $familyNamePayload = $familyName === null ? 'null' : '"' . $familyName . '"';
        $client->request(
            'POST',
            '/api/external/trial/create_account',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"proposedGivenName":' . $givenNamePayload . ', "proposedFamilyName":' . $familyNamePayload . '}'
        );

        return $client->getResponse();
    }
}

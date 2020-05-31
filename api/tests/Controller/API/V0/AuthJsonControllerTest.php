<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthJsonControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\API\V0\AuthJsonController::login
     *
     * @throws JsonException
     */
    public function testAuthJson(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/v0/auth/json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode(
                [
                    'email' => TestsFixtures::ADMIN_EMAIL,
                    'password' => TestsFixtures::ADMIN_PASSWORD,
                ],
                JSON_THROW_ON_ERROR
            )
        );

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('content-type', 'application/json');

        $response = $client->getResponse()->getContent();
        $this->assertJson($response);

        $data = \json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals(TestsFixtures::ADMIN_EMAIL, $data['email']);
    }
}

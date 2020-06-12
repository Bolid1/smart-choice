<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\DataFixtures\TestsFixtures;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\SecurityController::login()
     */
    public function testLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        static::assertResponseStatusCodeSame(200);

        $client->submitForm('submit', [
            'email' => TestsFixtures::ADMIN_EMAIL,
            'password' => TestsFixtures::ADMIN_PASSWORD,
        ]);

        static::assertResponseStatusCodeSame(302);
    }

    /**
     * @covers \App\Controller\SecurityController::logout
     */
    public function testLogout(): void
    {
        $controller = new SecurityController();
        $this->expectException(LogicException::class);
        $controller->logout();
    }
}

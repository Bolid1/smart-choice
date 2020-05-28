<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\IndexController::index
     */
    public function testIndex(): void
    {
        static::createClient()->request('GET', '/register');

        static::assertResponseStatusCodeSame(200);
    }
}

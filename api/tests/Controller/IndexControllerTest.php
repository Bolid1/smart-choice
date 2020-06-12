<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\IndexController::index
     */
    public function testIndex(): void
    {
        static::createClient()->request('GET', '/');

        static::assertResponseStatusCodeSame(200);
    }
}

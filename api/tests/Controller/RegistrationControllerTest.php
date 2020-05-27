<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\RegistrationController::register
     */
    public function testRegister(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        static::assertResponseStatusCodeSame(200);

        $client->submitForm('submit', [
            'registration_form[email]' => 'register@registration.tests',
            'registration_form[plainPassword]' => 'password',
            'registration_form[agreeTerms]' => '1',
        ]);

        static::assertResponseStatusCodeSame(302);
    }
}

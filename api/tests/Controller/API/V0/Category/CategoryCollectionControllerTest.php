<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Category;

use App\Entity\Category;
use App\Test\ApiTestCase;

class CategoryCollectionControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Extension\CategoryExtension::__construct()
     * @covers \App\Security\Extension\CategoryExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAdminGetCategoryCollection(): void
    {
        static::createCompanyAdminClient()->request('GET', '/api/v0/categories');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@id' => '/api/v0/categories',
                '@type' => 'hydra:Collection',
                // Admin can see his categories, and invitations of companies, where he is admin.
                'hydra:member' => [
                    [
                        '@type' => 'https://schema.org/category',
                    ],
                    [
                        '@type' => 'https://schema.org/category',
                    ],
                ],
                'hydra:totalItems' => 2,
            ],
            Category::class
        );
    }

    /**
     * @covers \App\Security\Extension\CategoryExtension::__construct()
     * @covers \App\Security\Extension\CategoryExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testUserGetCategoryCollection(): void
    {
        static::createAuthenticatedClient()->request('GET', '/api/v0/categories');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@id' => '/api/v0/categories',
                '@type' => 'hydra:Collection',
                // User can see his categories
                'hydra:member' => [
                    [
                        '@type' => 'https://schema.org/category',
                    ],
                    [
                        '@type' => 'https://schema.org/category',
                    ],
                ],
                'hydra:totalItems' => 2,
            ],
            Category::class
        );
    }

    /**
     * @covers \App\Security\Extension\CategoryExtension::__construct()
     * @covers \App\Security\Extension\CategoryExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/categories');
        static::assertResponseIsForbidden();
    }
}

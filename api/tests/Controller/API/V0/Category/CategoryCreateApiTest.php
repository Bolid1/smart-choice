<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Category;

use App\Entity\Category;
use App\Entity\Company;
use App\Test\ApiTestCase;

class CategoryCreateApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     * @covers \App\DataPersister\CategoryDataPersister::__construct()
     * @covers \App\DataPersister\CategoryDataPersister::supports()
     * @covers \App\DataPersister\CategoryDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateParentCategory(): void
    {
        // Create one category
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/categories',
            [
                'json' => $data = [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Richards family']
                    ),
                    'name' => $name = 'My new category',
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@type' => 'https://schema.org/category',
                'left' => 5,
            ] + $data,
            Category::class
        );

        // Then creates new category in another company
        static::createAnotherAdminClient()->request(
            'POST',
            '/api/v0/categories',
            [
                'json' => $data = [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Corporation LTD']
                    ),
                    'name' => $name = 'Another category',
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@type' => 'https://schema.org/category',
                'left' => 5,
            ] + $data,
            Category::class
        );
    }

    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateCategoryAsUser(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/categories',
            [
                'json' => [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Richards family']
                    ),
                    'currency' => $currency = 'RUB',
                    'name' => $name = 'Second card',
                    'balance' => $balance = 123546.67,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateCategoryToAnotherCompany(): void
    {
        // Admin can add any registered user to company
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/categories',
            [
                'json' => [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Corporation LTD']
                    ),
                    'currency' => $currency = 'RUB',
                    'name' => $name = 'Second card',
                    'balance' => $balance = 123546.67,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }
}

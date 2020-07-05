<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Category;

use App\Entity\Category;
use App\Test\ApiTestCase;

class CategoryPatchApiTest extends ApiTestCase
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
    public function testPatchCategory(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->getCategoryIri(),
            [
                'json' => [
                    'name' => $name = 'new name',
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@type' => 'https://schema.org/category',
                'name' => $name,
            ],
            Category::class
        );
    }

    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchCategoryAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->getCategoryIri(),
            [
                'json' => [
                    'name' => 'new name',
                ],
            ],
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
    public function testPatchCategoryAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->getCategoryIri(),
            [
                'json' => [
                    'name' => 'new name',
                ],
            ],
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getCategoryIri(): ?string
    {
        return $this->findIriBy(
            Category::class,
            [
                'name' => 'Category',
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Category;

use App\Entity\Category;
use App\Test\ApiTestCase;

class CategoryDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     * @covers \App\DataPersister\CategoryDataPersister::__construct()
     * @covers \App\DataPersister\CategoryDataPersister::supports()
     * @covers \App\DataPersister\CategoryDataPersister::remove()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteCategory(): void
    {
        static::createCompanyAdminClient()->request(
            'DELETE',
            $this->getCategoryIri(),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteCategoryAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->getCategoryIri(),
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
    public function testDeleteCategoryAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->getCategoryIri(),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getCategoryIri(): string
    {
        return $this->findIriBy(
            Category::class,
            [
                'name' => 'Category',
            ],
        );
    }
}

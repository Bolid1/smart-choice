<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Category;

use App\Entity\Category;
use App\Test\ApiTestCase;

class CategoryViewControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewCategory(): void
    {
        static::createCompanyAdminClient()->request(
            'GET',
            $this->getCategoryIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@type' => 'https://schema.org/category',
                'name' => 'Category',
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
    public function testViewCategoryAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->getCategoryIri(),
        );
        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\Voter\CategoryVoter::supports()
     * @covers \App\Security\Voter\CategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewCategoryAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->getCategoryIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Category',
                '@type' => 'https://schema.org/category',
                'name' => 'Category',
            ],
            Category::class
        );
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

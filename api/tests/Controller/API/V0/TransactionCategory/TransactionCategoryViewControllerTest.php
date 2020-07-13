<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\TransactionCategory;

use App\Entity\TransactionCategory;
use App\Test\ApiTestCase;

class TransactionCategoryViewControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewTransactionCategory(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->getTransactionCategoryIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/TransactionCategory',
                '@type' => 'TransactionCategory',
            ],
            TransactionCategory::class
        );
    }

    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewTransactionCategoryAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->getTransactionCategoryIri(),
        );
        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getTransactionCategoryIri(): ?string
    {
        return $this->findIriByCompany(TransactionCategory::class);
    }
}

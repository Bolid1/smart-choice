<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\TransactionCategory;

use App\Entity\TransactionCategory;
use App\Test\ApiTestCase;

class TransactionCategoryDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteTransactionCategory(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->getTransactionCategoryIri(),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteTransactionCategoryAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->getTransactionCategoryIri(),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteTransactionCategoryOfAnother(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->findIriByCompany(TransactionCategory::class, static::ANOTHER_COMPANY_NAME),
        )
        ;

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

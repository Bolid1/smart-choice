<?php

declare(strict_types=1);

namespace App\Doctrine\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * User can view & manage only self properties.
 */
final class UserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if ($this->supports($resourceClass)) {
            $this->addWhere($queryBuilder);
        }
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
        if ($this->supports($resourceClass)) {
            $this->addWhere($queryBuilder);
        }
    }

    /**
     * @param string $resourceClass
     *
     * @return bool
     */
    private function supports(string $resourceClass): bool
    {
        return User::class === $resourceClass;
    }

    private function addWhere(QueryBuilder $queryBuilder): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user = $this->security->getUser()) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq("{$rootAlias}.id", ':current_user'))
                ->setParameter('current_user', $user)
            ;
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->isNull("{$rootAlias}.id"));
        }
    }
}

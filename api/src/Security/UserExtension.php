<?php

declare(strict_types=1);

namespace App\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * User can view & manage only self properties.
 */
final class UserExtension implements QueryCollectionExtensionInterface
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
        if (User::class === $resourceClass) {
            $this->addWhere($queryBuilder);
        }
    }

    private function addWhere(QueryBuilder $queryBuilder): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user = $this->security->getUser()) {
            $queryBuilder
                ->andWhere("{$rootAlias}.id = :current_user")
                ->setParameter('current_user', $user)
            ;
        }/* else {
            Access denied by annotation
            "security"="is_granted('ROLE_USER')"
        }*/
    }
}

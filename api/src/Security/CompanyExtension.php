<?php

declare(strict_types=1);

namespace App\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * User has access to companies by relation Right.
 */
final class CompanyExtension implements QueryCollectionExtensionInterface
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
        if (Company::class === $resourceClass) {
            $user = $this->security->getUser();
            $rootAlias = $queryBuilder->getRootAliases()[0];

            if ($user instanceof User) {
                $queryBuilder
                    ->andWhere("{$rootAlias}.id in (:user_companies)")
                    ->setParameter('user_companies', $user->getCompanies())
                ;
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull("{$rootAlias}.id"));
            }
        }
    }
}

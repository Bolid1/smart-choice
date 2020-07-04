<?php

declare(strict_types=1);

namespace App\Security\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CategoryExtension implements QueryCollectionExtensionInterface
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
        if (Category::class === $resourceClass) {
            $user = $this->security->getUser();
            $rootAlias = $queryBuilder->getRootAliases()[0];

            if ($user instanceof User) {
                $queryBuilder
                    ->andWhere("{$rootAlias}.company in (:user_companies)")
                    ->setParameter('user_companies', $user->getCompanies())
                ;
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull("{$rootAlias}.id"));
            }
        }
    }
}

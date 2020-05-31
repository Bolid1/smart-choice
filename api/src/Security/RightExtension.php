<?php

declare(strict_types=1);

namespace App\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Right;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * User has access to companies by relation Right.
 */
final class RightExtension implements QueryCollectionExtensionInterface
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
        if (Right::class === $resourceClass) {
            $this->addWhere($queryBuilder);
        }
    }

    private function addWhere(QueryBuilder $queryBuilder): void
    {
        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user instanceof User) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        "{$rootAlias}.user = :current_user",
                        "{$rootAlias}.company in (:companies_where_user_is_admin)",
                    ),
                )
                ->setParameter('current_user', $user)
                ->setParameter(
                    'companies_where_user_is_admin',
                    $user
                        ->getRights()
                        ->filter(
                            static function (Right $right) {
                                return $right->isAdmin();
                            }
                        )
                        ->map(
                            static function (Right $right) {
                                return $right->getCompany();
                            }
                        )
                )
            ;
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->isNull("{$rootAlias}.user"));
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Invitation;
use App\Entity\Right;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 * User has access to companies by relation Invitation.
 */
final class InvitationExtension implements QueryCollectionExtensionInterface
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
        if (Invitation::class === $resourceClass) {
            $user = $this->security->getUser();
            $rootAlias = $queryBuilder->getRootAliases()[0];

            if ($user instanceof User) {
                $queryBuilder
                    ->andWhere("{$rootAlias}.toCompany in (:companies_where_user_is_admin)")
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
                $queryBuilder->andWhere($queryBuilder->expr()->isNull("{$rootAlias}.id"));
            }
        }
    }
}

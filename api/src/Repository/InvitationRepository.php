<?php

namespace App\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Security\InvitationExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    private InvitationExtension $extension;

    public function __construct(ManagerRegistry $registry, InvitationExtension $extension)
    {
        $this->extension = $extension;
        parent::__construct($registry, Invitation::class);
    }

    public function findByCompany(Company $company)
    {
        $queryBuilder = $this
            ->createQueryBuilder('invitation')
            ->where("invitation.toCompany = :company")
            ->setParameter('company', $company)
        ;

        $this->extension->applyToCollection(
            $queryBuilder,
            new QueryNameGenerator(),
            $this->getClassName(),
            'get'
        );

        return $queryBuilder->getQuery()->execute();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Company;
use App\Entity\Right;
use App\Security\RightExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Right|null find($id, $lockMode = null, $lockVersion = null)
 * @method Right|null findOneBy(array $criteria, array $orderBy = null)
 * @method Right[]    findAll()
 * @method Right[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RightRepository extends ServiceEntityRepository
{
    private RightExtension $extension;

    public function __construct(ManagerRegistry $registry, RightExtension $extension)
    {
        $this->extension = $extension;
        parent::__construct($registry, Right::class);
    }

    public function findByCompany(Company $company)
    {
        $queryBuilder = $this
            ->createQueryBuilder('right')
            ->where("right.company = :company")
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

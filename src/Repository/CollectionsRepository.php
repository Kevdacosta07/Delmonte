<?php

namespace App\Repository;

use App\Entity\Collections;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collections>
 */
class CollectionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collections::class);
    }

    public function findOneByYear(int $year): ?Collections
    {
        return $this->createQueryBuilder('u')
            ->where('u.year = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllSortedByYear()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.year', 'ASC') // Change 'ASC' to 'DESC' for descending order
            ->getQuery()
            ->getResult();
    }

    public function findAllSortedByYearDesc()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.year', 'DESC') // Change 'ASC' to 'DESC' for descending order
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Collections[] Returns an array of Collections objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Collections
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

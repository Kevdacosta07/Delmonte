<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    /**
     * @param string $collection_id L'identifiant de la collection
     * @return Products[] Retourne un tableau de produits associés à la collection spécifiée
     */
    public function findByCollectionId(string $collection_id): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.collection_id= :collection_id')
            ->setParameter('collection_id', $collection_id)
            ->getQuery()
            ->getResult();
    }

    public function deleteByCollectionId($collection_id)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->delete()
            ->where('e.collection_id = :collection_id')
            ->setParameter('collection_id', $collection_id);

        return $qb->getQuery()->execute();
    }

//    /**
//     * @return Products[] Returns an array of Products objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Products
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Dsi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dsi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dsi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dsi[]    findAll()
 * @method Dsi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DsiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dsi::class);
    }

    // /**
    //  * @return Dsi[] Returns an array of Dsi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Dsi
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

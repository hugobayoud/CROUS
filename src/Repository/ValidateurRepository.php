<?php

namespace App\Repository;

use App\Entity\Validateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Validateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Validateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Validateur[]    findAll()
 * @method Validateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Validateur::class);
    }

    // /**
    //  * @return Validateur[] Returns an array of Validateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Validateur
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
	*/
	
	public function findOneByIdUserAndService($id_user, $id_service): ?Validateur
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.id_user = :id_user')
            ->andWhere('v.id_service = :id_service')
            ->setParameters([
				'id_user' => $id_user,
				'id_service' => $id_service
				])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

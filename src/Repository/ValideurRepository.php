<?php

namespace App\Repository;

use App\Entity\Valideur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Valideur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Valideur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Valideur[]    findAll()
 * @method Valideur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValideurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Valideur::class);
    }

    // /**
    //  * @return Valideur[] Returns an array of Valideur objects
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
    public function findOneBySomeField($value): ?Valideur
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
	*/
	
	public function findByOrderByDateDeb(array $criteria)
	{
		$result = $this->findBy($criteria);

		if (count($result) > 1) {
			usort($result, function($a, $b) {
				$ad = $a->getDateDeb();
				$bd = $b->getDateDeb();
			
			 	if ($ad == $bd) {
			 	  return 0;
			 	}
			
			 	return $ad < $bd ? -1 : 1;
			});
		}

		return $result;
	}
}

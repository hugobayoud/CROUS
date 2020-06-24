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
	
	public function findAllMini()
	{
		$conn = $this->getEntityManager()->getConnection();
		$sql = 'SELECT dsi.user_id, dsi.date_deb, dsi.date_fin FROM Dsi';
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		return $stmt->fetchAll();
	}

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

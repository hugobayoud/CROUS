<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

	/**
	 * @return User[]
	 */
	public function findAllNotValidated(): array
	{
		// automatically knows to select Users
		// the "p" is an alias you'll use in the rest of the query
		$qb = $this->createQueryBuilder('p')
			->where('p.activation_token IS NOT NULL')
			->orderBy('p.date_deb_valid', 'ASC');
	
		$query = $qb->getQuery();
	
		return $query->execute();
	
		// to get just one result:
		// $product = $query->setMaxResults(1)->getOneOrNullResult();
	}

		/**
	 * @return User[]
	 */
	public function findAllValidated(): array
	{
		// automatically knows to select Users
		// the "p" is an alias you'll use in the rest of the query
		$qb = $this->createQueryBuilder('p')
			->where('p.activation_token IS NULL')
			->orderBy('p.date_deb_valid', 'ASC');
	
		$query = $qb->getQuery();
	
		return $query->execute();
	
		// to get just one result:
		// $product = $query->setMaxResults(1)->getOneOrNullResult();
	}

	/**
	 * Retourne un tableau de Services pour un utilisateur donné
	 */
    public function getServicesByOneUser(User $user)
    {
        $query = $this->createQueryBuilder('p')
            ->join('AppBundle\Entity\Color', 'c')
            ->where('c.product = :product')
            ->setParameter('product', )
            ->getQuery()
            ->getResult();

        return $query;
    }
	
	// /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
	*/
}

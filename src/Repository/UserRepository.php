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
		$qb = $this->createQueryBuilder('p')
			->where('p.activation_token IS NOT NULL')
			->orderBy('p.date_deb_valid', 'ASC');
	
		$query = $qb->getQuery();
	
		return $query->execute();
	}

	/**
	 * @return User[]
	 */
	public function findAllValidated(int $id = NULL): array
	{
		if (!is_null($id)) {
			$qb = $this->createQueryBuilder('p')
				->where('p.activation_token IS NULL')
				->andwhere('p.id != :id')
				->setParameter('id', $id)
				->orderBy('p.date_deb_valid', 'ASC');
		} else {
			$qb = $this->createQueryBuilder('p')
				->where('p.activation_token IS NULL')
				->orderBy('p.date_deb_valid', 'ASC');
		}

		return $qb->getQuery()->execute();
	}

	/**
	 * @return User[]
	 */
	public function findAllValidatedByNameASC(int $id = NULL): array
	{
		if (!is_null($id)) {
			$qb = $this->createQueryBuilder('p')
				->where('p.activation_token IS NULL')
				->andwhere('p.id != :id')
				->setParameter('id', $id)
				->add('orderBy','p.nom ASC, p.prenom ASC')
				;
		} else {
			$qb = $this->createQueryBuilder('p')
				->where('p.activation_token IS NULL')
				->orderBy('p.nom', 'ASC');
		}

		return $qb->getQuery()->execute();
	}

	
	/**
	 * Récupérer tous les users d'un service donné
	 * @return User[]
	 */
	public function findAllByServiceId(int $serviceId): array
	{
		$conn = $this->getEntityManager()->getConnection();
		$sql = "SELECT *
				FROM User u
				JOIN user_service us ON us.user_id = u.id
				WHERE us.service_id = $serviceId";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		
		return $stmt->fetchAll();
	}

	/**
	 * Donne le nombre de compte qui ne sont pas encore validés par un admin
	 * 
	 * @return int|NULL
	 */
    public function countNewAccount(): ?int
    {
		$query = $this->createQueryBuilder('p')
			->select('count(p.id)')
			->where('p.activation_token IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $query;
	}

	/**
	 * Donne le nombre de compte qui ne sont déjà validés (qui se retoruvent dans la page "gestion des utilisateurs")
	 * 
	 * @return int|NULL
	 */
    public function countAccount(): ?int
    {
		$query = $this->createQueryBuilder('p')
			->select('count(p.id)')
			->where('p.activation_token IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return $query;
	}

}

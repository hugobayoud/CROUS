<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
	 * Retourne tous les utilisateurs dont le compte n'a pas été validé
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
	 * Récupère tous les utilisateurs dont le compte a été validé par ordre croissant sur le nom, le prenom puis l'adresse mail
	 * @return User[]
	 */
	public function findAllValidated(int $id = NULL): array
	{
		$query = $this->createQueryBuilder('u');
		$query->where('u.activation_token IS NULL');

		if (!is_null($id)) {
			$query
				->andwhere('u.id != :id')
				->setParameter('id', $id);
		}
			
		$query->add('orderBy','u.nom ASC, u.prenom ASC, u.email ASC');
		return $query->getQuery()->getResult();
	}

	/**
	 * Récupère tous les utilisateurs dont le compte a été validé par ordre croissant sur le nom, le prenom puis l'adresse mail sans les ADMIN
	 * @return User[]
	 */
	public function findAllValidatedWithoutAdmin(int $id = NULL): array
	{
		$query = $this->createQueryBuilder('u');
		$query
			->where('u.activation_token IS NULL')
			->andWhere($query->expr()->notLike('u.roles', ':role'))
			->setParameter('role', '%ROLE_ADMIN%');

		if (!is_null($id)) {
			$query
				->andwhere('u.id != :id')
				->setParameter('id', $id);
		}

		$query
			->add('orderBy','u.nom ASC, u.prenom ASC, u.email ASC');
		return $query->getQuery()->getResult();
	}

	/**
	 * Donne le nombre de compte qui ne sont pas encore validés par un admin
	 * 
	 * @return int|NULL
	 */
    public function countNewAccounts(): ?int
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

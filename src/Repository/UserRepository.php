<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql as DqlFunctions;

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
	 * Récupère tous les utilisateurs dont le compte a été validé par ordre croissant sur le nom, le prenom puis l'adresse maiil
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
		//$config = new \Doctrine\ORM\Configuration();
		//$config->addCustomStringFunction(DqlFunctions\JsonContains::FUNCTION_NAME, DqlFunctions\JsonContains::class);

		//$em = EntityManager::create($dbParams, $config);
		//$queryBuilder = $em->createQueryBuilder();

		// $query = $this->createQueryBuilder('u');
		// $query
		// 	->where('u.activation_token IS NULL')
		// 	//->andWhere('JSON_CONTAINS(u.roles, "ROLE_ADMIN") = 1');
		// 	->andWhere($query->expr()->like('u.roles', ':role'))
		// 	->setParameter('role', "ROLE_ADMIN");

		// if (!is_null($id)) {
		// 	$query
		// 		->andwhere('u.id != :id')
		// 		->setParameter('id', $id);
		// }
			
		// $query->add('orderBy','u.nom ASC, u.prenom ASC, u.email ASC');
		$query = $this->createQueryBuilder('u');
		$query
			->where('u.activation_token IS NULL')
			->andWhere($query->expr()->notLike('u.roles', ':role'))
			->setParameter('role', '%ROLE_ADMIN%')
			->add('orderBy','u.nom ASC, u.prenom ASC, u.email ASC');
		return $query->getQuery()->getResult();
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

<?php
namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

	/**
	 * Retourne l'ensemble des applications triées par ordre croissant selon le code unique
	 * @return Application[]
	 */
	public function findAllCodeASC(): array
	{
		$query = $this->createQueryBuilder('a');
		$query->add('orderBy','a.code ASC');

		return $query->getQuery()->getResult();
	}
}

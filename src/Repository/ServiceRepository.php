<?php
namespace App\Repository;

use App\Entity\Service;
use App\Data\SearchData;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }
	
	/**
	 * Récupère les services en lien avec une recherche
	 * @return Service[]
	 */
	public function findSearch(SearchData $search): array
	{
		$query = $this
			->createQueryBuilder('p');

		if (!empty($search->q)) {
			$query = $query
			->where('p.code LIKE :q')
			->orWhere('p.libelle_court LIKE :q')
			->orWhere('p.libelle_long LIKE :q')
			->setParameter('q', "%{$search->q}%")
			;
		}
		return $query->getQuery()->getResult();
	}
}

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
	 * @param SearchData : toutes les données qui sont les contraintes pour la requête SQL
	 * @param int : id de l'utilisateur pour ne faire la recherche que sur les services dont il fait partie
	 * @return Service[]
	 */
	public function findSearch(SearchData $search): array
	{
		$query = $this->createQueryBuilder('s');

		if (!empty($search->q)) {
			$query = $query
				->where('s.code LIKE :q')
				->orWhere('s.libelle_court LIKE :q')
				->orWhere('s.libelle_long LIKE :q')
				->setParameter('q', "%{$search->q}%")
				;
		}

		return $query->getQuery()->getResult();
	}

	/**
	 * Retourne l'ensemble des service triés par ordre croissant selon le code unique
	 * @return Service[]
	 */
	public function findAllCodeASC(): array
	{
		$query = $this->createQueryBuilder('s');
		$query->add('orderBy','s.code ASC');

		return $query->getQuery()->getResult();
	}
}

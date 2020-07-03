<?php

namespace App\Repository;

use PDO;
use App\Entity\Demande;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Demande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demande[]    findAll()
 * @method Demande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
	}
	
	/**
	 * retourne le nombre de demandes avec un état bien précis (0,1 ou 2)
	 * 
	 * @return int|NULL
	 */
    public function countDemandsState(int $state, array $services = NULL): ?int
    {
		if (is_null($services)) {
			$query = $this->createQueryBuilder('p')
			->select('count(p.id)')
			->where('p.etat = :state')
			->setParameter('state', $state)
			;
		} else {
			$query = $this->createQueryBuilder('p')
			->select('count(p.id)');


			foreach ($services as $service) {
				$query = $query
					->orWhere('p.service = :service')
					->setParameter('service', $service);
			}

			$query = $query->andWhere('p.etat = :state')
			->setParameter('state', $state);
		}


        return $query->getQuery()->getSingleScalarResult();
	}
}

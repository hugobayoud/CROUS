<?php
namespace App\Repository;

use App\Entity\DroitEffectif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DroitEffectif|null find($id, $lockMode = null, $lockVersion = null)
 * @method DroitEffectif|null findOneBy(array $criteria, array $orderBy = null)
 * @method DroitEffectif[]    findAll()
 * @method DroitEffectif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DroitEffectifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DroitEffectif::class);
	}
}

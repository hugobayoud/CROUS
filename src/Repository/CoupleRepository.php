<?php
namespace App\Repository;

use PDO;
use App\Entity\Couple;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Couple|null find($id, $lockMode = null, $lockVersion = null)
 * @method Couple|null findOneBy(array $criteria, array $orderBy = null)
 * @method Couple[]    findAll()
 * @method Couple[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoupleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Couple::class);
	}
}

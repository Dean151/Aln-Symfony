<?php

namespace App\Repository;

use App\Entity\AlnMeal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlnMeal>
 *
 * @method AlnMeal|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlnMeal|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlnMeal[]    findAll()
 * @method AlnMeal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AlnMealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlnMeal::class);
    }

    public function add(AlnMeal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlnMeal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

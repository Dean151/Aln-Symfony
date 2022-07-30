<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AlnManualMeal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlnManualMeal>
 *
 * @method AlnManualMeal|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlnManualMeal|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlnManualMeal[]    findAll()
 * @method AlnManualMeal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AlnManualMealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlnManualMeal::class);
    }

    public function add(AlnManualMeal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlnManualMeal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

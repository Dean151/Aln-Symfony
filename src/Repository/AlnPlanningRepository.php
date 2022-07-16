<?php

namespace App\Repository;

use App\Entity\AlnPlanning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlnPlanning>
 *
 * @method AlnPlanning|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlnPlanning|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlnPlanning[]    findAll()
 * @method AlnPlanning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlnPlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlnPlanning::class);
    }

    public function add(AlnPlanning $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlnPlanning $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

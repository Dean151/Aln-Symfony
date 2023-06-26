<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AlnAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlnAlert>
 *
 * @method AlnAlert|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlnAlert|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlnAlert[]    findAll()
 * @method AlnAlert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AlnAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlnAlert::class);
    }

    public function add(AlnAlert $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlnAlert $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

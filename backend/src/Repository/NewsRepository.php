<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function findLatestActiveNotification(): ?News
    {
        return $this->createQueryBuilder('n')
            ->where('n.publish = :publish')
            ->andWhere('n.push = :push')
            ->andWhere('n.notificationEndDate IS NULL OR n.notificationEndDate > :now')
            ->setParameter('publish', true)
            ->setParameter('push', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('n.notificationDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
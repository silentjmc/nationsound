<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the News entity.
 * 
 * This class is responsible for providing methods to query and retrieve News entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the News
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<News>
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    /**
     * NewsRepository constructor.
     *
     * Initializes the repository with the News entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * Finds the latest active notification that is published and has not ended.
     * 
     * An active push notification is defined as a News item that:
     * 1. Is published (`publishNews = true`).
     * 2. Has push notifications enabled (`push = true`).
     * 3. Either has no notification end date (`notificationEndDate IS NULL`)
     *    OR its notification end date (`notificationEndDate`) is in the future.
     *
     * The method orders the results by the `notificationDate` in descending order
     * (most recent first) and returns only the single latest matching News item.
     * 
     *
     * @return News|null Returns the latest active News entity or null if none found.
     * @throws \Doctrine\ORM\NonUniqueResultException If more than one result is found.
     */
    public function findLatestActiveNotification(): ?News
    {
        return $this->createQueryBuilder('n')
            ->where('n.publishNews = :publishNews')
            ->andWhere('n.push = :push')
            ->andWhere('n.notificationEndDate IS NULL OR n.notificationEndDate > :now')
            ->setParameter('publishNews', true)
            ->setParameter('push', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('n.notificationDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
<?php

namespace App\Repository;

use App\Entity\EventDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the EventDate entity.
 * This class is responsible for providing methods to query and retrieve EventDate entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * 
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the EventDate
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<EventDate> * 
 * @method EventDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventDate|null findOneBy(array $criteria, array $orderBy = null) 
 * @method EventDate[]    findAll()
 * @method EventDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventDateRepository extends ServiceEntityRepository
{
    /**
     * EventDateRepository constructor.
     *
     * Initializes the repository with the EventDate entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventDate::class);
    }
}
<?php

namespace App\Repository;

use App\Entity\EventType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the EventType entity.
 * 
 * This class is responsible for providing methods to query and retrieve EventType entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the EventType
 * entity can be added here.
 * 
 * @extends ServiceEntityRepository<EventType>
 * @method EventType|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventType|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventType[]    findAll()
 * @method EventType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventTypeRepository extends ServiceEntityRepository
{
    /**
     * EventTypeRepository constructor.
     *
     * Initializes the repository with the EventType entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventType::class);
    }
}
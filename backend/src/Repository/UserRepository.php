<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for the User entity.
 *
 * This class is responsible for providing methods to query and retrieve User entities
 * from the database. It extends the base ServiceEntityRepository, which provides
 * common repository functionalities.
 *
 * Standard Doctrine methods like find(), findAll(), findBy(), and findOneBy() are
 * available through the parent class. Custom query methods specific to the User
 * entity can be added here.
 *
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     *
     * Initializes the repository with the User entity class and the Doctrine ManagerRegistry.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
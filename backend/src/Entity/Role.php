<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    /**
     * The unique database identifier for the role.
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idRole = null;

    /**
     * The name of the role, e.g., 'ROLE_ADMIN', 'ROLE_USER'.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $role = null;

    /**
     * The collection of users associated with this role.
     * This is a one-to-many relationship where one role can be assigned to many users.
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'roleUser')]
    private Collection $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the role name as a string.
     * * @return string The role name or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->role ?? '';
    }

    /**
     * Returns the unique identifier of the role.
     * @return int|null The ID of the role or null if not set.
     * 
     */
    public function getIdRole(): ?int
    {
        return $this->idRole;
    }

    /**
     * Sets the unique identifier for the role.
     * @param int $idRole The ID to set for the role.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdRole(int $idRole): static
    {
        $this->idRole = $idRole;

        return $this;
    }

    /**
     * Returns the name of the role.
     * @return string|null The role name or null if not set.
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * Sets the name of the role.
     * @param string $role The role name to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Returns the collection of users associated with this role.
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    /**
     * Adds a user to the role.
     * If the user is not already associated with a role, it adds the user and sets the role for the user.
     * @param User $user The user to add to the role.
     * @return static Returns the current instance for method chaining.
     */
    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setRoleUser($this);
        }

        return $this;
    }

    /**
     * Removes a user from the role.
     * If the user is currently associated with a role, it removes the user and sets the role for the user to null.
     * @param User $user The user to remove from the role.
     * @return static Returns the current instance for method chaining.
     */
    public function removeUser(User $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getRoleUser() === $this) {
                $user->setRoleUser(null);
            }
        }

        return $this;
    }
}

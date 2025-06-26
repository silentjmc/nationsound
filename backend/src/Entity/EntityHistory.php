<?php

namespace App\Entity;

use App\Repository\EntityHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntityHistoryRepository::class)]
class EntityHistory
{
    /**
     * The unique database identifier for the entity history record.
     * This is used to uniquely identify each record in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idEntityHistory = null;

    /**
     * The name of the entity that was modified, e.g., 'Event', 'Artist'.
     * This is used to identify which entity type the history record pertains to.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $entityName = null;

    /**
     * The unique identifier of the item of entity that was modified.
     * This is used to link the history record to a specific item of the entity.
     *
     * @var int|null
     */
    #[ORM\Column]
    private ?int $entityId = null;

    /**
     * The action performed on the entity, e.g., 'create', 'update', 'delete'.
     * This is used to describe what kind of modification was made to the entity.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $action = null;

    /**
     * The old values of the entity before the modification.
     * This is stored as a JSON string to capture the state of the entity prior to the change.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $oldValues = null;

    /**
     * The new values of the entity after the modification.
     * This is stored as a JSON string to capture the state of the entity after the change.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $newValues = null;

    /**
     * The date and time when the action was performed.
     * This is used to track when the modification occurred.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAction = null;

    /**
     * The user who performed the action on the entity.
     * This is used for auditing purposes to know who made the change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $user = null;

    /**
     * Returns the unique identifier of the entity history record.
     *
     * @return int|null The ID of the entity history record or null if not set.
     */
    public function getIdEntityHistory(): ?int
    {
        return $this->idEntityHistory;
    }

    /**
     * Sets the unique identifier for the entity history record.
     *
     * @param int $idEntityHistory The ID to set for the entity history record.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdEntityHistory(int $idEntityHistory): static
    {
        $this->idEntityHistory = $idEntityHistory;

        return $this;
    }

    /**
     * Returns the name of the entity that was modified.
     *
     * @return string|null The name of the entity or null if not set.
     */
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    /**
     * Sets the name of the entity that was modified.
     *
     * @param string $entityName The name of the entity to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setEntityName(string $entityName): static
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Returns the unique identifier of the entity that was modified.
     *
     * @return int|null The ID of the entity or null if not set.
     */
    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    /**
     * Sets the unique identifier for the entity that was modified.
     *
     * @param int $entityId The ID to set for the entity.
     * @return static Returns the current instance for method chaining.
     */
    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Returns the action performed on the entity.
     *
     * @return string|null The action performed or null if not set.
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * Sets the action performed on the entity.
     *
     * @param string $action The action to set, e.g., 'create', 'update', 'delete'.
     * @return static Returns the current instance for method chaining.
     */
    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Returns the old values of the entity before the modification.
     *
     * @return array|null The old values as an associative array or null if not set.
     */
    public function getOldValues(): ?array
    {
        return $this->oldValues ? json_decode($this->oldValues, true) : null;
    }

    /**
     * Sets the old values of the entity before the modification.
     * The values are stored as a JSON string.
     *
     * @param array|null $oldValues The old values to set, or null if there are no old values.
     * @return static Returns the current instance for method chaining.
     */
    public function setOldValues(?array $oldValues): static
    {
        $this->oldValues = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;

        return $this;
    }

    /**
     * Returns the new values of the entity after the modification.
     *
     * @return array|null The new values as an associative array or null if not set.
     */
    public function getNewValues(): ?array
    {
        return $this->newValues ? json_decode($this->newValues, true) : null;
    }

    /**
     * Sets the new values of the entity after the modification.
     * The values are stored as a JSON string.
     *
     * @param array|null $newValues The new values to set, or null if there are no new values.
     * @return static Returns the current instance for method chaining.
     */
    public function setNewValues(?array $newValues): static
    {
        $this->newValues = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

        return $this;
    }

    /**
     * Returns the date and time when the action was performed.
     *
     * @return \DateTimeInterface|null The date and time of the action or null if not set.
     */
    public function getDateAction(): ?\DateTimeInterface
    {
        return $this->dateAction;
    }

    /**
     * Sets the date and time when the action was performed.
     *
     * @param \DateTimeInterface $dateAction The date and time to set for the action.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateAction(\DateTimeInterface $dateAction): static
    {
        $this->dateAction = $dateAction;

        return $this;
    }

    /**
     * Returns the user who performed the action on the entity.
     *
     * @return string|null The username of the user or null if not set.
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * Sets the user who performed the action on the entity.
     * This is used for auditing purposes to know who made the change.
     *
     * @param string $user The username of the user to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setUser(string $user): static
    {
        $this->user = $user;

        return $this;
    }

}
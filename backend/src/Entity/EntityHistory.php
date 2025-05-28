<?php

namespace App\Entity;

use App\Repository\EntityHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntityHistoryRepository::class)]
class EntityHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idEntityHistory = null;

    #[ORM\Column(length: 255)]
    private ?string $entityName = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $oldValues = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $newValues = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAction = null;

    #[ORM\Column(length: 255)]
    private ?string $user = null;

    public function getIdEntityHistory(): ?int
    {
        return $this->idEntityHistory;
    }

    public function setIdEntityHistory(int $idEntityHistory): static
    {
        $this->idEntityHistory = $idEntityHistory;

        return $this;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): static
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getOldValues(): ?array
    {
        return $this->oldValues ? json_decode($this->oldValues, true) : null;
    }

    public function setOldValues(?array $oldValues): static
    {
        $this->oldValues = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;

        return $this;
    }

    public function getNewValues(): ?array
    {
        return $this->newValues ? json_decode($this->newValues, true) : null;
    }

    public function setNewValues(?array $newValues): static
    {
        $this->newValues = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

        return $this;
    }

    public function getDateAction(): ?\DateTimeInterface
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTimeInterface $dateAction): static
    {
        $this->dateAction = $dateAction;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;

        return $this;
    }

}
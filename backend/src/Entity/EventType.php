<?php

namespace App\Entity;

use App\Repository\EventTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventTypeRepository::class)]
class EventType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idEventType = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEvent", "getArtist"])]
    private ?string $nameType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEventType = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationEventType = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'type')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    // Méthode pour convertir l'objet en chaîne
    public function __toString(): string
    {
        return $this->nameType ?? '';
    }

    public function getIdEventType(): ?int
    {
        return $this->idEventType;
    }

    public function setIdEventType(int $idEventType): static
    {
        $this->idEventType = $idEventType;

        return $this;
    }

    public function getNameType(): ?string
    {
        return $this->nameType;
    }

    public function setNameType(string $nameType): static
    {
        $this->nameType = $nameType;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setType($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getType() === $this) {
                $event->setType(null);
            }
        }

        return $this;
    }

    public function getDateModificationEventType(): ?\DateTimeInterface
    {
        return $this->dateModificationEventType;
    }

    public function setDateModificationEventType(\DateTimeInterface $dateModificationEventType): static
    {
        $this->dateModificationEventType = $dateModificationEventType;

        return $this;
    }

    public function getUserModificationEventType(): ?string
    {
        return $this->userModificationEventType;
    }

    public function setUserModificationEventType(string $userModificationEventType): static
    {
        $this->userModificationEventType = $userModificationEventType;

        return $this;
    }
}

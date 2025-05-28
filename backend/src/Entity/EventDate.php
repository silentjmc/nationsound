<?php

namespace App\Entity;

use App\Repository\EventDateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventDateRepository::class)]
class EventDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idEventDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getEvent", "getArtist"])]
    private ?\DateTimeInterface $date = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'date')]
    private Collection $events;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEventDate = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationEventDate = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->date instanceof DateTime ? $this->date->format('d/m/Y') : '';
    }

    public function getDateToString(): string
    {
        return $this->__toString();
    }
    
    public function getIdEventDate(): ?int
    {
        return $this->idEventDate;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

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
            $event->setDate($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getDate() === $this) {
                $event->setDate(null);
            }
        }

        return $this;
    }

    public function getDateModificationEventDate(): ?\DateTimeInterface
    {
        return $this->dateModificationEventDate;
    }

    public function setDateModificationEventDate(\DateTimeInterface $dateModificationEventDate): static
    {
        $this->dateModificationEventDate = $dateModificationEventDate;

        return $this;
    }

    public function getUserModificationEventDate(): ?string
    {
        return $this->userModificationEventDate;
    }

    public function setUserModificationEventDate(string $userModificationEventDate): static
    {
        $this->userModificationEventDate = $userModificationEventDate;

        return $this;
    }
}

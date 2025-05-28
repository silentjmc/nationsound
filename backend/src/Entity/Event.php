<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEvent"])]
    private ?int $idEvent = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(["getEvent", "getArtist"])]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(["getEvent", "getArtist"])]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_event_type')]
    #[Groups(["getEvent", "getArtist"])]
    private ?EventType $type = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_artist')]
    #[Groups(["getEvent"])]
    private ?Artist $artist = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_event_date')]
    #[Groups(["getEvent", "getArtist"])]
    private ?EventDate $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEvent = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationEvent = null;

    #[ORM\Column]
    #[Groups(["getEvent", "getArtist"])]
    private ?bool $publishEvent = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_event_location')]
    #[Groups(["getEvent", "getArtist"])]
    private ?EventLocation $eventLocation = null;

    public function __toString(): string
    {
        return $this->date ?? '';
    }

    public function getIdEvent(): ?int
    {
        return $this->idEvent;
    }

    public function setIdEvent(int $idEvent): static
    {
        $this->idEvent = $idEvent;

        return $this;
    }


    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeInterface $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeInterface $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(?EventType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getDate(): ?EventDate
    {
        return $this->date;
    }

    public function setDate(?EventDate $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDateModificationEvent(): ?\DateTimeInterface
    {
        return $this->dateModificationEvent;
    }

    public function setDateModificationEvent(\DateTimeInterface $dateModificationEvent): static
    {
        $this->dateModificationEvent = $dateModificationEvent;

        return $this;
    }

    public function getUserModificationEvent(): ?string
    {
        return $this->userModificationEvent;
    }

    public function setUserModificationEvent(string $userModificationEvent): static
    {
        $this->userModificationEvent = $userModificationEvent;

        return $this;
    }

    public function isPublishEvent(): ?bool
    {
        return $this->publishEvent;
    }

    public function setPublishEvent(bool $publishEvent): static
    {
        $this->publishEvent = $publishEvent;

        return $this;
    }

    public function getEventLocation(): ?EventLocation
    {
        return $this->eventLocation;
    }

    public function setEventLocation(?EventLocation $eventLocation): static
    {
        $this->eventLocation = $eventLocation;

        return $this;
    }
}

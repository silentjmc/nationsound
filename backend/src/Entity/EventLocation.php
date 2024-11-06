<?php

namespace App\Entity;

use App\Repository\EventLocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventLocationRepository::class)]
class EventLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEventLocation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation", "getEvent"])]
    private ?string $locationName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 14)]
    #[Groups(["getEventLocation"])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 14)]
    #[Groups(["getEventLocation"])]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getEventLocation"])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(length: 255)]
    private ?string $userModification = null;

    #[ORM\Column]
    private ?bool $publish = null;

    #[ORM\ManyToOne(inversedBy: 'eventLocations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getEventLocation"])]
    private ?LocationType $typeLocation = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'eventLocation')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    public function setLocationName(string $locationName): static
    {
        $this->locationName = $locationName;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getUserModification(): ?string
    {
        return $this->userModification;
    }

    public function setUserModification(string $userModification): static
    {
        $this->userModification = $userModification;

        return $this;
    }

    public function isPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): static
    {
        $this->publish = $publish;

        return $this;
    }

    public function getTypeLocation(): ?LocationType
    {
        return $this->typeLocation;
    }

    public function setTypeLocation(?LocationType $typeLocation): static
    {
        $this->typeLocation = $typeLocation;

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
            $event->setEventLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getEventLocation() === $this) {
                $event->setEventLocation(null);
            }
        }

        return $this;
    }
}

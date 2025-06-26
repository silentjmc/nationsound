<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    /**
     * The unique database identifier for the event.
     * This is used to uniquely identify each event in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEvent"])]
    private ?int $idEvent = null;

    /**
     * The start time of the event.
     * This is used to store the starting time of the event in a DateTime format.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(["getEvent", "getArtist"])]
    private ?\DateTimeInterface $heureDebut = null;

    /**
     * The end time of the event.
     * This is used to store the ending time of the event in a DateTime format.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(["getEvent", "getArtist"])]
    private ?\DateTimeInterface $heureFin = null;

    /**
     * The type of the event.
     * This is a many-to-one relationship where each event is associated with one event type.
     * The event type can be something like 'Concert', 'Workshop', etc.
     *
     * @var EventType|null
     */
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_event_type')]
    #[Groups(["getEvent", "getArtist"])]
    private ?EventType $type = null;

    /**
     * The artist associated with the event.
     * This is a many-to-one relationship where each event is associated with one artist.
     * The artist can be a musician, speaker, or any individual performing at the event.
     *
     * @var Artist|null
     */
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_artist')]
    #[Groups(["getEvent"])]
    private ?Artist $artist = null;

    /**
     * The date of the event.
     * This is a many-to-one relationship where each event is associated with one event date.
     * The event date can be a specific day when the event occurs.
     *
     * @var EventDate|null
     */
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_event_date')]
    #[Groups(["getEvent", "getArtist"])]
    private ?EventDate $date = null;

    /**
     * The date when the event was last modified.
     * This is used to track changes to the event's information.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEvent = null;

    /**
     * The user who last modified the event.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationEvent = null;

    /**
     * Indicates whether the event is published or not.
     * This is used to control the visibility of the event in the application.
     *
     * @var bool|null
     */
    #[ORM\Column]
    #[Groups(["getEvent", "getArtist"])]
    private ?bool $publishEvent = null;

    /**
     * The location of the event.
     * This is a many-to-one relationship where each event is associated with one event location.
     * The event location can be a venue, hall, or any place where the event takes place.
     *
     * @var EventLocation|null
     */
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_event_location')]
    #[Groups(["getEvent", "getArtist"])]
    private ?EventLocation $eventLocation = null;

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the date as a string.
     *
     * @return string The date or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->date ?? '';
    }

    /**
     * Returns the unique identifier of the event.
     * This is used to uniquely identify each event in the database.
     *
     * @return int|null The ID of the event or null if not set.
     */
    public function getIdEvent(): ?int
    {
        return $this->idEvent;
    }

    /**
     * Sets the unique identifier for the event.
     * This is used to uniquely identify each event in the database.
     *
     * @param int $idEvent The ID to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdEvent(int $idEvent): static
    {
        $this->idEvent = $idEvent;

        return $this;
    }

    /**
     * Returns the start time of the event.
     * This is used to get the starting time of the event in a DateTime format.
     *
     * @return \DateTimeInterface|null The start time of the event or null if not set.
     */
    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    /**
     * Sets the start time of the event.
     * This method is used to set the starting time of the event in a DateTime format.
     *
     * @param \DateTimeInterface $heureDebut The start time to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setHeureDebut(\DateTimeInterface $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    /**
     * Returns the end time of the event.
     * This is used to get the ending time of the event in a DateTime format.
     *
     * @return \DateTimeInterface|null The end time of the event or null if not set.
     */
    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    /**
     * Sets the end time of the event.
     * This method is used to set the ending time of the event in a DateTime format.
     *
     * @param \DateTimeInterface $heureFin The end time to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setHeureFin(\DateTimeInterface $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    /**
     * Returns the type of the event.
     * This is used to get the event type, which can be something like 'Concert', 'Workshop', etc.
     *
     * @return EventType|null The type of the event or null if not set.
     */
    public function getType(): ?EventType
    {
        return $this->type;
    }

    /**
     * Sets the type of the event.
     * This method is used to set the event type, which can be something like 'Concert', 'Workshop', etc.
     *
     * @param EventType|null $type The type to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setType(?EventType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the artist associated with the event.
     * This is used to get the artist, who can be a musician, speaker, or any individual performing at the event.
     *
     * @return Artist|null The artist associated with the event or null if not set.
     */
    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    /**
     * Sets the artist associated with the event.
     * This method is used to set the artist, who can be a musician, speaker, or any individual performing at the event.
     *
     * @param Artist|null $artist The artist to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Returns the date of the event.
     * This is used to get the event date, which can be a specific day when the event occurs.
     *
     * @return EventDate|null The date of the event or null if not set.
     */
    public function getDate(): ?EventDate
    {
        return $this->date;
    }

    /**
     * Sets the date of the event.
     * This method is used to set the event date, which can be a specific day when the event occurs.
     *
     * @param EventDate|null $date The date to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setDate(?EventDate $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the date when the event was last modified.
     * This is used to track changes to the event's information.
     *
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationEvent(): ?\DateTimeInterface
    {
        return $this->dateModificationEvent;
    }

    /**
     * Sets the date when the event was last modified.
     * This is used to track changes to the event's information.
     *
     * @param \DateTimeInterface $dateModificationEvent The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationEvent(\DateTimeInterface $dateModificationEvent): static
    {
        $this->dateModificationEvent = $dateModificationEvent;

        return $this;
    }

    /**
     * Returns the user who last modified the event.
     * This is used for auditing purposes to know who made the last change.
     *
     * @return string|null The username of the user who last modified the event or null if not set.
     */
    public function getUserModificationEvent(): ?string
    {
        return $this->userModificationEvent;
    }

    /**
     * Sets the user who last modified the event.
     * This is used for auditing purposes to know who made the last change.
     *
     * @param string $userModificationEvent The username of the user who last modified the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationEvent(string $userModificationEvent): static
    {
        $this->userModificationEvent = $userModificationEvent;

        return $this;
    }

    /**
     * Returns whether the event is published or not.
     * This is used to control the visibility of the event in the application.
     *
     * @return bool|null True if the event is published, false otherwise, or null if not set.
     */
    public function isPublishEvent(): ?bool
    {
        return $this->publishEvent;
    }

    /**
     * Sets whether the event is published or not.
     * This is used to control the visibility of the event in the application.
     *
     * @param bool $publishEvent True to publish, false to unpublish.
     * @return static Returns the current instance for method chaining.
     */
    public function setPublishEvent(bool $publishEvent): static
    {
        $this->publishEvent = $publishEvent;

        return $this;
    }

    /**
     * Returns the location of the event.
     * This is used to get the event location, which can be a venue, hall, or any place where the event takes place.
     *
     * @return EventLocation|null The location of the event or null if not set.
     */
    public function getEventLocation(): ?EventLocation
    {
        return $this->eventLocation;
    }

    /**
     * Sets the location of the event.
     * This method is used to set the event location, which can be a venue, hall, or any place where the event takes place.
     *
     * @param EventLocation|null $eventLocation The location to set for the event.
     * @return static Returns the current instance for method chaining.
     */
    public function setEventLocation(?EventLocation $eventLocation): static
    {
        $this->eventLocation = $eventLocation;

        return $this;
    }
}
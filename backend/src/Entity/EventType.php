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
    /**
     * The unique database identifier for the event type.
     * This is used to uniquely identify each event type in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idEventType = null;

    /**
     * The name of the event type, e.g., 'Concert', 'Rencontre'.
     * This is used to categorize different types of events in the application.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getEvent", "getArtist"])]
    private ?string $nameType = null;

    /**
     * The date when the event type was last modified.
     * This is used to track changes to the event type.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEventType = null;

    /**
     * The user who last modified the event type.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationEventType = null;

    /**
     * The collection of events associated with this event type.
     * This is a one-to-many relationship where one event type can have multiple events.
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'type')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of type as a string.
     *
     * @return string The name of type or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->nameType ?? '';
    }

    /**
     * Returns the unique identifier of the event type.
     * @return int|null The ID of the event type or null if not set.
     */
    public function getIdEventType(): ?int
    {
        return $this->idEventType;
    }

    /**
     * Sets the unique identifier for the event type.
     * This is typically used by the ORM and should not be set manually.
     *
     * @param int $idEventType The ID of the event type.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdEventType(int $idEventType): static
    {
        $this->idEventType = $idEventType;

        return $this;
    }

    /**
     * Returns the name of the event type.
     * This is used to identify the type of event in various contexts, such as forms or lists.
     *
     * @return string|null The name of the event type or null if not set.
     */
    public function getNameType(): ?string
    {
        return $this->nameType;
    }

    /**
     * Sets the name of the event type.
     * This is used to categorize different types of events, such as 'Concert', 'Exhibition', etc.
     *
     * @param string $nameType The name to set for the event type.
     * @return static Returns the current instance for method chaining.
     */
    public function setNameType(string $nameType): static
    {
        $this->nameType = $nameType;

        return $this;
    }

    /**
     * Returns the collection of events associated with this event type.
     * This is a one-to-many relationship where one event type can have multiple events.
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * Adds an event to the collection of events associated with this event type.
     * This method ensures that the event is not already in the collection before adding it.
     *
     * @param Event $event The event to add to the collection.
     * @return static Returns the current instance for method chaining.
     */
    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setType($this);
        }

        return $this;
    }

    /**
     * Removes an event from the collection of events associated with this event type.
     * This method ensures that the relationship is properly maintained on both sides.
     *
     * @param Event $event The event to remove from the collection.
     * @return static Returns the current instance for method chaining.
     */
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

    /**
     * Returns the date when the event type was last modified.
     * This is used to track changes to the event type and can be useful for auditing purposes.
     *
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationEventType(): ?\DateTimeInterface
    {
        return $this->dateModificationEventType;
    }

    /**
     * Sets the date when the event type was last modified.
     * This is used to track changes to the event type and can be useful for auditing purposes.
     *
     * @param \DateTimeInterface $dateModificationEventType The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationEventType(\DateTimeInterface $dateModificationEventType): static
    {
        $this->dateModificationEventType = $dateModificationEventType;

        return $this;
    }

    /**
     * Returns the user who last modified the event type.
     * This is used for auditing purposes to know who made the last change.
     *
     * @return string|null The username of the user who last modified the event type or null if not set.
     */
    public function getUserModificationEventType(): ?string
    {
        return $this->userModificationEventType;
    }

    /**
     * Sets the user who last modified the event type.
     * This is used for auditing purposes to know who made the last change.
     *
     * @param string $userModificationEventType The username of the user who last modified the event type.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationEventType(string $userModificationEventType): static
    {
        $this->userModificationEventType = $userModificationEventType;

        return $this;
    }
}
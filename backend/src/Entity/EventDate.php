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
    /**
     * The unique database identifier for the event date.
     * This is used to uniquely identify each event date in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idEventDate = null;

    /**
     * The date of the event.
     * This is used to store the actual date of the event in a DateTime format.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["getEvent", "getArtist"])]
    private ?\DateTimeInterface $date = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'date')]
    private Collection $events;

    /**
     * The date when the event date was last modified.
     * This is used to track changes to the event date.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEventDate = null;

    /**
     * The user who last modified the event date.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationEventDate = null;

    /**
     * Constructor to initialize the events collection.
     * This ensures that the collection is always ready to use when a new EventDate object is created.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the date as a string.
     *
     * @return string The date or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->date instanceof DateTime ? $this->date->format('d/m/Y') : '';
    }

    /**
     * Returns the date as a string for use in other contexts, such as API responses.
     * This method is useful when you need a formatted date string.
     *
     * @return string The date formatted as a string.
     */
    public function getDateToString(): string
    {
        return $this->__toString();
    }
    
    /**
     * Returns the unique identifier of the event date.
     *
     * @return int|null The ID of the event date or null if not set.
     */
    public function getIdEventDate(): ?int
    {
        return $this->idEventDate;
    }

    /**
     * Sets the unique identifier for the event date.
     *
     * @param int $idEventDate The ID to set for the event date.
     * @return static Returns the current instance for method chaining.
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Sets the date for the event date.
     * This method is used to set the date of the event date entity.
     *
     * @param \DateTimeInterface $date The date to set for the event date.
     * @return static Returns the current instance for method chaining.
     */
    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the collection of events associated with this event date.
     * This method is used to retrieve all events that are linked to this specific date.
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * Adds an event to the collection of events for this event date.
     * This method is used to associate an event with this specific date.
     *
     * @param Event $event The event to add.
     * @return static Returns the current instance for method chaining.
     */
    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setDate($this);
        }

        return $this;
    }

    /**
     * Removes an event from the collection of events for this event date.
     * This method is used to disassociate an event from this specific date.
     *
     * @param Event $event The event to remove.
     * @return static Returns the current instance for method chaining.
     */
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

    /**
     * Returns the date when the event date was last modified.
     * This is used to track changes to the event date and can be useful for auditing purposes.
     *
     * @return \DateTimeInterface|null The modification date or null if not set.
     */
    public function getDateModificationEventDate(): ?\DateTimeInterface
    {
        return $this->dateModificationEventDate;
    }

    /**
     * Sets the date when the event date was last modified.
     * This is used to track changes to the event date and can be useful for auditing purposes.
     *
     * @param \DateTimeInterface $dateModificationEventDate The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationEventDate(\DateTimeInterface $dateModificationEventDate): static
    {
        $this->dateModificationEventDate = $dateModificationEventDate;

        return $this;
    }

    /**
     * Returns the user who last modified the event date.
     * This is used for auditing purposes to know who made the last change.
     *
     * @return string|null The username of the user who last modified the event date or null if not set.
     */
    public function getUserModificationEventDate(): ?string
    {
        return $this->userModificationEventDate;
    }

    /**
     * Sets the user who last modified the event date.
     * This is used for auditing purposes to know who made the last change.
     *
     * @param string $userModificationEventDate The username of the user who last modified the event date.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationEventDate(string $userModificationEventDate): static
    {
        $this->userModificationEventDate = $userModificationEventDate;

        return $this;
    }
}
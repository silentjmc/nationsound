<?php

namespace App\Entity;

use App\Repository\EventLocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventLocationRepository::class)]
class EventLocation
{
    /**
     * The unique database identifier for the event location.
     * This is used to uniquely identify each event location in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEventLocation"])]
    private ?int $idEventLocation = null;

    /**
     * The name of the event location, e.g., 'Scène Metal', 'Toilettes Nord'.
     * This is used to display the name of the location in the application.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation", "getEvent","getArtist"])]
    private ?string $nameEventLocation = null;

    /**
     * The latitude of the event location.
     * This is used to store the geographical latitude coordinate of the location.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 14)]
    #[Groups(["getEventLocation"])]
    private ?string $latitude = null;

    /**
     * The longitude of the event location.
     * This is used to store the geographical longitude coordinate of the location.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 14)]
    #[Groups(["getEventLocation"])]
    private ?string $longitude = null;

    /**
     * The content or description of the event location.
     * This is used to provide additional information about the location on map.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getEventLocation"])]
    private ?string $contentEventLocation = null;

    /**
     * The date when the event location was last modified.
     * This is used to track changes to the event location.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEventLocation = null;

    /**
     * The user who last modified the event location.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationEventLocation = null;

    /**
     * Indicates whether the event location is published or not.
     * This is used to control the visibility of the event location in the application.
     *
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $publishEventLocation = null;

    /**
     * The type of the event location, e.g., 'Scène', 'Toilettes'.
     * This is used to categorize the location and can be used for filtering or displaying in the application.
     *
     * @var LocationType|null
     */
    #[ORM\ManyToOne(inversedBy: 'eventLocations')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_location_type')]
    #[Groups(["getEventLocation"])]
    private ?LocationType $typeLocation = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'eventLocation')]
    private Collection $events;

    /**
     * Constructor to initialize the events collection.
     * This is necessary to avoid null reference errors when adding or removing events.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of the event location as a string.
     *
     * @return string The name of the event location or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->nameEventLocation ?? '';
    }

    /**
     * Returns the unique identifier of the event location.
     * This is used to uniquely identify each event location in the database.
     *
     * @return int|null The ID of the event location or null if not set.
     */
    public function getIdEventLocation(): ?int
    {
        return $this->idEventLocation;
    }

    /**
     * Sets the unique identifier for the event location.
     * This is used to uniquely identify each event location in the database.
     *
     * @param int $idEventLocation The ID to set for the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdEventLocation(int $idEventLocation): static
    {
        $this->idEventLocation = $idEventLocation;
        return $this;
    }

    /**
     * Returns the name of the event location.
     * This is used to display the name of the location in the application.
     *
     * @return string|null The name of the event location or null if not set.
     */
    public function getNameEventLocation(): ?string
    {
        return $this->nameEventLocation;
    }

    /**
     * Sets the name of the event location.
     * This is used to display the name of the location in the application.
     *
     * @param string $nameEventLocation The name to set for the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setNameEventLocation(string $nameEventLocation): static
    {
        $this->nameEventLocation = $nameEventLocation;

        return $this;
    }

    /**
     * Returns the latitude of the event location.
     * This is used to store the geographical latitude coordinate of the location.
     *
     * @return string|null The latitude of the event location or null if not set.
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude of the event location.
     * This is used to store the geographical latitude coordinate of the location.
     *
     * @param string $latitude The latitude to set for the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Returns the longitude of the event location.
     * This is used to store the geographical longitude coordinate of the location.
     *
     * @return string|null The longitude of the event location or null if not set.
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude of the event location.
     * This is used to store the geographical longitude coordinate of the location.
     *
     * @param string $longitude The longitude to set for the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Returns the content or description of the event location.
     * This is used to provide additional information about the location on map.
     *
     * @return string|null The content of the event location or null if not set.
     */
    public function getContentEventLocation(): ?string
    {
        return $this->contentEventLocation;
    }

    /**
     * Sets the content or description of the event location.
     * This is used to provide additional information about the location on map.
     *
     * @param string $contentEventLocation The content to set for the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setContentEventLocation(string $contentEventLocation): static
    {
        $this->contentEventLocation = $contentEventLocation;

        return $this;
    }

    /**
     * Returns the date when the event location was last modified.
     * This is used to track changes to the event location and can be useful for auditing purposes.
     *
     * @return \DateTimeInterface|null The date of last modification or null if not set.
     */
    public function getDateModificationEventLocation(): ?\DateTimeInterface
    {
        return $this->dateModificationEventLocation;
    }

    /**
     * Sets the date when the event location was last modified.
     * This is used to track changes to the event location and can be useful for auditing purposes.
     *
     * @param \DateTimeInterface $dateModificationEventLocation The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationEventLocation(\DateTimeInterface $dateModificationEventLocation): static
    {
        $this->dateModificationEventLocation = $dateModificationEventLocation;

        return $this;
    }

    /**
     * Returns the user who last modified the event location.
     * This is used for auditing purposes to know who made the last change.
     *
     * @return string|null The username of the user who last modified the event location or null if not set.
     */
    public function getUserModificationEventLocation(): ?string
    {
        return $this->userModificationEventLocation;
    }

    /** 
     * Sets the user who last modified the event location.
     * This is used for auditing purposes to know who made the last change.
     *
     * @param string $userModificationEventLocation The username of the user who last modified the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationEventLocation(string $userModificationEventLocation): static
    {
        $this->userModificationEventLocation = $userModificationEventLocation;

        return $this;
    }

    /**
     * Returns whether the event location is published or not.
     * This is used to control the visibility of the event location in the application.
     *
     * @return bool|null True if the event location is published, false otherwise, or null if not set.
     */
    public function isPublishEventLocation(): ?bool
    {
        return $this->publishEventLocation;
    }

    /**
     * Sets whether the event location is published or not.
     * This is used to control the visibility of the event location in the application.
     *
     * @param bool $publishEventLocation True to publish, false to unpublish.
     * @return static Returns the current instance for method chaining.
     */
    public function setPublishEventLocation(bool $publishEventLocation): static
    {
        $this->publishEventLocation = $publishEventLocation;

        return $this;
    }

    /**
     * Returns the type of the event location.
     * This is used to categorize the location and can be used for filtering or displaying in the application.
     *
     * @return LocationType|null The type of the event location or null if not set.
     */
    public function getTypeLocation(): ?LocationType
    {
        return $this->typeLocation;
    }

    /**
     * Sets the type of the event location.
     * This is used to categorize the location and can be used for filtering or displaying in the application.
     *
     * @param LocationType|null $typeLocation The type to set for the event location.
     * @return static Returns the current instance for method chaining.
     */
    public function setTypeLocation(?LocationType $typeLocation): static
    {
        $this->typeLocation = $typeLocation;

        return $this;
    }

    /**
     * Returns the collection of events associated with this event location.
     * This method is used to retrieve all events that are linked to this specific location.
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * Adds an event to the collection of events for this event location.
     * This method is used to associate an event with this specific location.
     *
     * @param Event $event The event to add.
     * @return static Returns the current instance for method chaining.
     */
    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setEventLocation($this);
        }
        return $this;
    }

    /**
     * Removes an event from the collection of events for this event location.
     * This method is used to disassociate an event from this specific location.
     *
     * @param Event $event The event to remove.
     * @return static Returns the current instance for method chaining.
     */
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

    /**
     * Validates the unpublish action for the event location.
     * This method checks if the event location is currently published and has related published events.
     * If so, it adds a violation to the context to prevent direct unpublishing from the form.
     *
     * @param ExecutionContextInterface $context The validation context.
     * @param mixed $payload The payload being validated (not used in this case).
     */
    #[Assert\Callback]
     public function validateUnpublish(ExecutionContextInterface $context, $payload): void
    {

        if (!$this->isPublishEventLocation()) { // Si l'état actuel (soumis) est "non publié"
            $hasRelatedPublishedEvents = false;
            foreach ($this->getEvents() as $event) {
                if ($event->isPublishEvent()) { // Assurez-vous que Event a une méthode isPublishEvent()
                    $hasRelatedPublishedEvents = true;
                    break;
                }
            }

            if ($hasRelatedPublishedEvents) {
                $context->buildViolation(sprintf(
                    'Le lieu "%s" ne peut pas être dépublié directement depuis ce formulaire car il a des événements publiés liés. Veuillez utiliser l\'action "Dépublier" depuis la liste des lieux, qui gérera la dépublication des événements associés.',
                    $this->getNameEventLocation() ?? 'ce lieu' // Utilisez le nom du lieu si disponible
                ))
                ->atPath('publishEventLocation') // Lie l'erreur au champ "Publié"
                ->addViolation();
            }
        }
    }
}
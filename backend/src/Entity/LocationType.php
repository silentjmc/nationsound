<?php

namespace App\Entity;

use App\Repository\LocationTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationTypeRepository::class)]
class LocationType
{
    /**
     * The unique database identifier for the location type.
     * This is used to uniquely identify each location type in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEventLocation"])]
    private ?int $idLocationType = null;

    /**
     * The name of the location type, e.g., 'Scène Metal', 'Toilettes'.
     * This is used to display the type of location in the application.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation"])]
    private ?string $nameLocationType = null;

    /**
     * The symbol associated with the location type.
     * This is used to visually represent the type of location in the map of the application.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation"])]
    private ?string $symbol = null;
    
    /**
     * The date when the location type was last modified.
     * This is used to track changes to the location type.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateModificationLocationType = null;

    /**
     * The user who last modified the location type.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userModificationLocationType = null;

    /**
     * The collection of event locations associated with this type.
     * This is a one-to-many relationship where one location type can have multiple event locations.
     * @var Collection<int, EventLocation>
     */
    #[ORM\OneToMany(targetEntity: EventLocation::class, mappedBy: 'typeLocation')]
    private Collection $eventLocations;

    /**
     * Constructor to initialize the event locations collection.
     * This ensures that the collection is always ready to use when a new LocationType object is created.
     */
    public function __construct()
    {
        $this->eventLocations = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of the location type as a string.
     *
     * @return string The name of the location type or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->nameLocationType ?? '';
    }

    /**
     * Returns the unique identifier of the location type.
     *
     * @return int|null The ID of the location type or null if not set.
     */
    public function getIdLocationType(): ?int
    {
        return $this->idLocationType;
    }

    /**
     * Sets the unique identifier for the location type.
     *
     * @param int $idLocationType The ID to set for the location type.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdLocationType(int $idLocationType): static
    {
        $this->idLocationType = $idLocationType;

        return $this;
    }

    /**
     * Returns the name of the location type.
     *
     * @return string|null The name of the location type or null if not set.
     */
    public function getNameLocationType(): ?string
    {
        return $this->nameLocationType;
    }

    /**
     * Sets the name of the location type.
     *
     * @param string $nameLocationType The name to set for the location type.
     * @return static Returns the current instance for method chaining.
     */
    public function setNameLocationType(string $nameLocationType): static
    {
        $this->nameLocationType = $nameLocationType;

        return $this;
    }

    /**
     * Returns the symbol associated with the location type.
     *
     * @return string|null The symbol of the location type or null if not set.
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * Sets the symbol associated with the location type.
     * This is used to visually represent the type of location in the map of the application.
     *
     * @param string $symbol The symbol to set for the location type.
     * @return static Returns the current instance for method chaining.
     */
    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Returns the date when the location type was last modified.
     * This is used to track changes to the location type item and can be useful for auditing purposes.
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationLocationType(): ?\DateTimeInterface
    {
        return $this->dateModificationLocationType;
    }

    /**
     * Sets the date when the location type was last modified.
     * This is used to track changes to the location type item and can be useful for auditing purposes.
     * @param \DateTimeInterface|null $dateModificationLocationType The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationLocationType(?\DateTimeInterface $dateModificationLocationType): self
    {
        $this->dateModificationLocationType = $dateModificationLocationType;
        return $this;
    }

    /**
     * Returns the user who last modified the location type.
     * This is used for auditing purposes to know who made the last change.
     * @return string|null The username of the user who last modified the location type or null if not set.
     */
    public function getUserModificationLocationType(): ?string
    {
        return $this->userModificationLocationType;
    }

    /**
     * Sets the user who last modified the location type.
     * This is used for auditing purposes to know who made the last change.
     * @param string|null $userModificationLocationType The username of the user who last modified the location type.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationLocationType(?string $userModificationLocationType): self
    {
        $this->userModificationLocationType = $userModificationLocationType;
        return $this;
    }

    /**
     * Returns the collection of event locations associated with this type.
     * This is a one-to-many relationship where one location type can have multiple event locations.
     * @return Collection<int, EventLocation>
     */
    public function getEventLocations(): Collection
    {
        return $this->eventLocations;
    }

    /**
     * Adds an event location to the collection of event locations.
     * This method ensures that the relationship is properly established between the location type and the event location.
     *
     * @param EventLocation $eventLocation The event location to add.
     * @return static Returns the current instance for method chaining.
     */
    public function addEventLocation(EventLocation $eventLocation): static
    {
        if (!$this->eventLocations->contains($eventLocation)) {
            $this->eventLocations->add($eventLocation);
            $eventLocation->setTypeLocation($this);
        }
        return $this;
    }

    /**
     * Removes an event location from the collection of event locations.
     * This method ensures that the relationship is properly maintained on both sides.
     * If the event location being removed is the owning side, it sets the typeLocation to null.
     *
     * @param EventLocation $eventLocation The event location to remove.
     * @return static Returns the current instance for method chaining.
     */
    public function removeEventLocation(EventLocation $eventLocation): static
    {
        if ($this->eventLocations->removeElement($eventLocation)) {
            // set the owning side to null (unless already changed)
            if ($eventLocation->getTypeLocation() === $this) {
                $eventLocation->setTypeLocation(null);
            }
        }
        return $this;
    }
}
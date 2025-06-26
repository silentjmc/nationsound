<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    /**
     * The unique database identifier for the artist.
     * This is used to uniquely identify each artist in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEvent","getArtist"])]
    private ?int $idArtist = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEvent","getArtist"])]
    private ?string $nameArtist = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getEvent","getArtist"])]
    private ?string $contentArtist = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEvent","getArtist"])]
    private ?string $imageArtist = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEvent","getArtist"])]
    private ?string $thumbnail = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEvent","getArtist"])]
    private ?string $typeMusic = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'artist')]
    #[Groups(["getArtist"])]
    private Collection $events;

    /**
     * The date when the artist was last modified.
     * This is used to track changes to the artist's information.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationArtist = null;

    /**
     * The user who last modified the artist's information.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationArtist = null;

    /**
     * Constructor to initialize the events collection.
     * This ensures that the collection is always ready to use when a new Artist object is created.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of artist as a string.
     *
     * @return string The name of artist or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->nameArtist ?? '';
    }

    /**
     * Returns the unique identifier of the artist.
     * @return int|null The ID of the artist or null if not set.
     */
    public function getIdArtist(): ?int
    {
        return $this->idArtist;
    }

    /**
     * Sets the unique identifier for the artist.
     * @param int $idArtist The ID to set for the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdArtist(int $idArtist): static
    {
        $this->idArtist = $idArtist;

        return $this;
    }

    /**
     * Returns the name of the artist.
     * @return string|null The name of the artist or null if not set.
     */
    public function getNameArtist(): ?string
    {
        return $this->nameArtist;
    }

    /**
     * Sets the name of the artist.
     * @param string $nameArtist The name to set for the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setNameArtist(string $nameArtist): static
    {
        $this->nameArtist = $nameArtist;

        return $this;
    }

    /**
     * Returns the content description of the artist.
     * This is used to provide additional information about the artist.
     *
     * @return string|null The content description of the artist or null if not set.
     */
    public function getContentArtist(): ?string
    {
        return $this->contentArtist;
    }

    /**
     * Sets the content description of the artist.
     * This is used to set the artist's description when creating or updating the artist.
     *
     * @param string $contentArtist The content to set for the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setContentArtist(string $contentArtist): static
    {
        $this->contentArtist = $contentArtist;

        return $this;
    }

    /**
     * Returns the image associated with the artist.
     * This is used to display the artist's image in the application.
     *
     * @return string|null The image URL of the artist or null if not set.
     */
    public function getImageArtist(): ?string
    {
        return $this->imageArtist;
    }

    /**
     * Sets the image associated with the artist.
     * This is used to set the artist's image when creating or updating the artist.
     *
     * @param string|null $imageArtist The image URL to set for the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setImageArtist(?string $imageArtist): static
    {
        $this->imageArtist = $imageArtist;

        return $this;
    }

    /**
     * Returns the type of music associated with the artist.
     * This is used to categorize the artist by their musical genre.
     *
     * @return string|null The type of music or null if not set.
     */
    public function getTypeMusic(): ?string
    {
        return $this->typeMusic;
    }

    /**
     * Sets the type of music associated with the artist.
     * This is used to set the artist's musical genre when creating or updating the artist.
     *
     * @param string $typeMusic The type of music to set for the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setTypeMusic(string $typeMusic): static
    {
        $this->typeMusic = $typeMusic;

        return $this;
    }

    /**
     * Returns the collection of events associated with the artist.
     * This is used to retrieve all events where the artist is featured.
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * Adds an event to the collection of events for this artist.
     * This method is used to associate an event with the artist.
     *
     * @param Event $event The event to add.
     * @return static Returns the current instance for method chaining.
     */
    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setArtist($this);
        }

        return $this;
    }

    /**
     * Removes an event from the collection of events for this artist.
     * This method is used to disassociate an event from the artist.
     *
     * @param Event $event The event to remove.
     * @return static Returns the current instance for method chaining.
     */
    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getArtist() === $this) {
                $event->setArtist(null);
            }
        }

        return $this;
    }

    /**
     * Returns the date when the artist was last modified.
     * This is used to track changes to the artist's information and can be useful for auditing purposes.
     *
     * @return \DateTimeInterface|null The date of last modification or null if not set.
     */
    public function getDateModificationArtist(): ?\DateTimeInterface
    {
        return $this->dateModificationArtist;
    }

    /**
     * Sets the date when the artist was last modified.
     * This is used to track changes to the artist's information and can be useful for auditing purposes.
     *
     * @param \DateTimeInterface $dateModificationArtist The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationArtist(\DateTimeInterface $dateModificationArtist): static
    {
        $this->dateModificationArtist = $dateModificationArtist;

        return $this;
    }

    /**
     * Returns the user who last modified the artist's information.
     * This is used for auditing purposes to know who made the last change.
     *
     * @return string|null The username of the user who last modified the artist or null if not set.
     */
    public function getUserModificationArtist(): ?string
    {
        return $this->userModificationArtist;
    }

    /** 
     * Sets the user who last modified the artist's information.
     * This is used for auditing purposes to know who made the last change.
     *
     * @param string $userModificationArtist The username of the user who last modified the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationArtist(string $userModificationArtist): static
    {
        $this->userModificationArtist = $userModificationArtist;

        return $this;
    }

    /**
     * Returns the thumbnail image associated with the artist.
     * This is used to display a smaller version of the artist's image in the application.
     *
     * @return string|null The thumbnail URL of the artist or null if not set.
     */
    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    /**
     * Sets the thumbnail image associated with the artist.
     * This is used to set the artist's thumbnail when creating or updating the artist.
     *
     * @param string $thumbnail The thumbnail URL to set for the artist.
     * @return static Returns the current instance for method chaining.
     */
    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * Filters the events collection to only include published events.
     * This method modifies the events collection to only keep events that are marked as published.
     * It is useful for displaying only relevant events in the application.
     */
    public function publishedEventsLinked(): void
    {
        $this->events = $this->events->filter(function(Event $event) {
            return $event->isPublishEvent();
        });
    }
}
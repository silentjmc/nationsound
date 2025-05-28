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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationArtist = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationArtist = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nameArtist ?? '';
    }

    public function getIdArtist(): ?int
    {
        return $this->idArtist;
    }

    public function setIdArtist(int $idArtist): static
    {
        $this->idArtist = $idArtist;

        return $this;
    }

    public function getNameArtist(): ?string
    {
        return $this->nameArtist;
    }

    public function setNameArtist(string $nameArtist): static
    {
        $this->nameArtist = $nameArtist;

        return $this;
    }

    public function getContentArtist(): ?string
    {
        return $this->contentArtist;
    }

    public function setContentArtist(string $contentArtist): static
    {
        $this->contentArtist = $contentArtist;

        return $this;
    }

    public function getImageArtist(): ?string
    {
        return $this->imageArtist;
    }

    public function setImageArtist(?string $imageArtist): static
    {
        $this->imageArtist = $imageArtist;

        return $this;
    }

    public function getTypeMusic(): ?string
    {
        return $this->typeMusic;
    }

    public function setTypeMusic(string $typeMusic): static
    {
        $this->typeMusic = $typeMusic;

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
            $event->setArtist($this);
        }

        return $this;
    }

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

    public function getDateModificationArtist(): ?\DateTimeInterface
    {
        return $this->dateModificationArtist;
    }

    public function setDateModificationArtist(\DateTimeInterface $dateModificationArtist): static
    {
        $this->dateModificationArtist = $dateModificationArtist;

        return $this;
    }

    public function getUserModificationArtist(): ?string
    {
        return $this->userModificationArtist;
    }

    public function setUserModificationArtist(string $userModificationArtist): static
    {
        $this->userModificationArtist = $userModificationArtist;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function publishedEventsLinked(): void
    {
        $this->events = $this->events->filter(function(Event $event) {
            return $event->isPublishEvent();
        });
    }
}

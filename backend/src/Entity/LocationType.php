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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEventLocation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation"])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation"])]
    private ?string $symbol = null;
    // test
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateModification = null;
    // test
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userModification = null;

    /**
     * @var Collection<int, EventLocation>
     */
    #[ORM\OneToMany(targetEntity: EventLocation::class, mappedBy: 'typeLocation')]
    private Collection $eventLocations;

    public function __construct()
    {
        $this->eventLocations = new ArrayCollection();
    }

    // Méthode pour convertir l'objet en chaîne
    public function __toString(): string
    {
        return $this->type ?? '';
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): self
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getUserModification(): ?string
    {
        return $this->userModification;
    }

    public function setUserModification(?string $userModification): self
    {
        $this->userModification = $userModification;
        return $this;
    }

    /**
     * @return Collection<int, EventLocation>
     */
    public function getEventLocations(): Collection
    {
        return $this->eventLocations;
    }

    public function addEventLocation(EventLocation $eventLocation): static
    {
        if (!$this->eventLocations->contains($eventLocation)) {
            $this->eventLocations->add($eventLocation);
            $eventLocation->setTypeLocation($this);
        }

        return $this;
    }

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


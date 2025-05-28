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
    private ?int $idLocationType = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation"])]
    private ?string $nameLocationType = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation"])]
    private ?string $symbol = null;
    
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateModificationLocationType = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userModificationLocationType = null;

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
        return $this->nameLocationType ?? '';
    }

    public function getIdLocationType(): ?int
    {
        return $this->idLocationType;
    }

    public function setIdLocationType(int $idLocationType): static
    {
        $this->idLocationType = $idLocationType;

        return $this;
    }

    public function getNameLocationType(): ?string
    {
        return $this->nameLocationType;
    }

    public function setNameLocationType(string $nameLocationType): static
    {
        $this->nameLocationType = $nameLocationType;

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

    public function getDateModificationLocationType(): ?\DateTimeInterface
    {
        return $this->dateModificationLocationType;
    }

    public function setDateModificationLocationType(?\DateTimeInterface $dateModificationLocationType): self
    {
        $this->dateModificationLocationType = $dateModificationLocationType;
        return $this;
    }

    public function getUserModificationLocationType(): ?string
    {
        return $this->userModificationLocationType;
    }

    public function setUserModificationLocationType(?string $userModificationLocationType): self
    {
        $this->userModificationLocationType = $userModificationLocationType;
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
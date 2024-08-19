<?php

namespace App\Entity;

use App\Repository\PartnerTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerTypeRepository::class)]
class PartnerType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @var Collection<int, Partners>
     */
    #[ORM\OneToMany(targetEntity: Partners::class, mappedBy: 'type')]
    private Collection $partners;

    public function __construct()
    {
        $this->partners = new ArrayCollection();
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

    /**
     * @return Collection<int, Partners>
     */
    public function getPartners(): Collection
    {
        return $this->partners;
    }

    public function addPartner(Partners $partner): static
    {
        if (!$this->partners->contains($partner)) {
            $this->partners->add($partner);
            $partner->setType($this);
        }

        return $this;
    }

    public function removePartner(Partners $partner): static
    {
        if ($this->partners->removeElement($partner)) {
            // set the owning side to null (unless already changed)
            if ($partner->getType() === $this) {
                $partner->setType(null);
            }
        }

        return $this;
    }
}

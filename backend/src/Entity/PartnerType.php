<?php

namespace App\Entity;

use App\Repository\PartnerTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartnerTypeRepository::class)]
class PartnerType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?int $idPartnerType = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $titlePartnerType = null;

    /**
     * @var Collection<int, Partner>
     */
    #[ORM\OneToMany(targetEntity: Partner::class, mappedBy: 'typePartner')]
    private Collection $partner;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationPartnerType = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationPartnerType = null;

    public function __construct()
    {
        $this->partner = new ArrayCollection();
    }
    
    // Méthode pour convertir l'objet en chaîne
    public function __toString(): string
    {
        return $this->titlePartnerType ?? '';
    }

    public function getIdPartnerType(): ?int
    {
        return $this->idPartnerType;
    }

    public function setIdPartnerType(int $idPartnerType): static
    {
        $this->idPartnerType = $idPartnerType;

        return $this;
    }

    public function getTitlePartnerType(): ?string
    {
        return $this->titlePartnerType;
    }

    public function setTitlePartnerType(string $titlePartnerType): static
    {
        $this->titlePartnerType = $titlePartnerType;

        return $this;
    }

    /**
     * @return Collection<int, Partner>
     */
    public function getPartner(): Collection
    {
        return $this->partner;
    }

    public function addPartner(Partner $partner): static
    {
        if (!$this->partner->contains($partner)) {
            $this->partner->add($partner);
            $partner->setTypePartner($this);
        }

        return $this;
    }

    public function removePartner(Partner $partner): static
    {
        if ($this->partner->removeElement($partner)) {
            // set the owning side to null (unless already changed)
            if ($partner->getTypePartner() === $this) {
                $partner->setTypePartner(null);
            }
        }
        return $this;
    }

    public function getDateModificationPartnerType(): ?\DateTimeInterface
    {
        return $this->dateModificationPartnerType;
    }

    public function setDateModificationPartnerType(\DateTimeInterface $dateModificationPartnerType): static
    {
        $this->dateModificationPartnerType = $dateModificationPartnerType;

        return $this;
    }

    public function getUserModificationPartnerType(): ?string
    {
        return $this->userModificationPartnerType;
    }

    public function setUserModificationPartnerType(string $userModificationPartnerType): static
    {
        $this->userModificationPartnerType = $userModificationPartnerType;

        return $this;
    }
}

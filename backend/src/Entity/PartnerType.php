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
    /**
     * The unique database identifier for the partner type.
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?int $idPartnerType = null;

    /**
     * The title of the partner type, e.g., 'Médias', 'Institutions'.
     * This is used to categorize different types of partners.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $titlePartnerType = null;

    /**
     * The collection of partners associated with this type.
     * This is a one-to-many relationship where one partner type can have multiple partners.
     * @var Collection<int, Partner>
     */
    #[ORM\OneToMany(targetEntity: Partner::class, mappedBy: 'typePartner')]
    private Collection $partner;

    /**
     * The date when the partner type was last modified.
     * This is used to track changes to the partner type.
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationPartnerType = null;

    /**
     * The user who last modified the partner type.
     * This is used for auditing purposes to know who made the last change.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationPartnerType = null;

    /**
     * Constructor to initialize the partner collection.
     * This ensures that the collection is always ready to use when a new PartnerType object is created.
     */
    public function __construct()
    {
        $this->partner = new ArrayCollection();
    }
    
    /**
     * This method is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the title of the partner type as a string.
     * @return string The title of the partner type or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->titlePartnerType ?? '';
    }

    /**
     * Returns the unique identifier of the partner type.
     * @return int|null The ID of the partner type or null if not set.
     */
    public function getIdPartnerType(): ?int
    {
        return $this->idPartnerType;
    }

    /**
     * Sets the unique identifier for the partner type.
     * @param int $idPartnerType The ID to set for the partner type.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdPartnerType(int $idPartnerType): static
    {
        $this->idPartnerType = $idPartnerType;

        return $this;
    }

    /**
     * Returns the title of the partner type.
     * @return string|null The title of the partner type or null if not set.
     */
    public function getTitlePartnerType(): ?string
    {
        return $this->titlePartnerType;
    }

    /**
     * Sets the title of the partner type.
     * This is used to categorize different types of partners, such as 'Médias', 'Institutions'.
     * @param string $titlePartnerType The title to set for the partner type.
     * @return static Returns the current instance for method chaining.
     */
    public function setTitlePartnerType(string $titlePartnerType): static
    {
        $this->titlePartnerType = $titlePartnerType;

        return $this;
    }

    /**
     * Returns the collection of partners associated with this partner type.
     * This is a one-to-many relationship where one partner type can have multiple partners.
     * @return Collection<int, Partner>
     */
    public function getPartner(): Collection
    {
        return $this->partner;
    }

    /**
     * Adds a partner to the collection of partners for this partner type.
     * This method ensures that the relationship is properly maintained on both sides.
     * @param Partner $partner The partner to add.
     * @return static Returns the current instance for method chaining.
     */
    public function addPartner(Partner $partner): static
    {
        if (!$this->partner->contains($partner)) {
            $this->partner->add($partner);
            $partner->setTypePartner($this);
        }

        return $this;
    }

    /**
     * Removes a partner from the collection of partners for this partner type.
     * This method ensures that the relationship is properly maintained on both sides.
     * If the partner being removed is the owning side, it sets the typePartner to null.
     * @param Partner $partner The partner to remove.
     * @return static Returns the current instance for method chaining.
     */
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

    /**
     * Returns the date when the partner type was last modified.
     * This is used to track changes to the partner type.
     * @return \DateTimeInterface|null The modification date or null if not set.
     */
    public function getDateModificationPartnerType(): ?\DateTimeInterface
    {
        return $this->dateModificationPartnerType;
    }

    /**
     * Sets the date when the partner type was last modified.
     * This is used to track changes to the partner type.
     * @param \DateTimeInterface $dateModificationPartnerType The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationPartnerType(\DateTimeInterface $dateModificationPartnerType): static
    {
        $this->dateModificationPartnerType = $dateModificationPartnerType;

        return $this;
    }

    /**
     * Returns the user who last modified the partner type.
     * This is used for auditing purposes to know who made the last change.
     * @return string|null The username of the user who last modified the partner type or null if not set.
     */
    public function getUserModificationPartnerType(): ?string
    {
        return $this->userModificationPartnerType;
    }

    /**
     * Sets the user who last modified the partner type.
     * This is used for auditing purposes to know who made the last change.
     * @param string $userModificationPartnerType The username of the user who last modified the partner type.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationPartnerType(string $userModificationPartnerType): static
    {
        $this->userModificationPartnerType = $userModificationPartnerType;

        return $this;
    }
}

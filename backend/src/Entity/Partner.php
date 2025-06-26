<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
class Partner
{
    /**
     * The unique database identifier for the partner.
     * This is used to uniquely identify each partner in the database.
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?int $idPartner = null;

    /**
     * The name of the partner, e.g., 'SACEM', 'EPSI Fotmation'.
     * This is used to display the partner's name in the application.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $namePartner = null;

    /**
     * The image associated with the partner, typically a logo or banner.
     * This is used to visually represent the partner in the application.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $imagePartner = null;

    /**
     * The URL associated with the partner, typically linking to their website or a relevant page.
     * This is used to provide quick access to more information about the partner.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $url = null;

    /**
     * The type of the partner, which categorizes the partner into a specific group (e.g., 'Médias', 'Institutions').
     * This is used to filter and organize partners in the application.
     * This is a many-to-one relationship where multiple partners can belong to one type.
     * @var PartnerType|null
     */
    #[ORM\ManyToOne(targetEntity: PartnerType::class, inversedBy: 'partner')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_partner_type',name: 'type_partner_id',)]
    #[Groups(["getPartners"])]
    private ?PartnerType $typePartner = null;

    /**
     * The date when the partner was last modified.
     * This is used to track changes to the partner's information.
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationPartner = null;

    /**
     * The user who last modified the partner's information.
     * This is used for auditing purposes to know who made the last change.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationPartner = null;

    /**
     * Indicates whether the partner is published or not.
     * This is used to control the visibility of the partner in the application.
     * @var bool|null
     */
    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?bool $publishPartner = null;

    /**
     * This method is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of the partner as a string.
     * @return string The name of the partner or an empty string if not set.
     * */
    public function __toString(): string
    {
        return $this->name_partner ?? '';
    }

    /**
     * Returns the unique identifier of the partner.
     * @return int|null The ID of the partner or null if not set.
     */
    public function getIdPartner(): ?int
    {
        return $this->idPartner;
    }

    /**
     * Sets the unique identifier for the partner.
     * @param int $idPartner The ID to set for the partner.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdPartner(int $idPartner): static
    {
        $this->idPartner = $idPartner;

        return $this;
    }

    /**
     * Returns the name of the partner.
     * @return string|null The name of the partner or null if not set.
     */
    public function getNamePartner(): ?string
    {
        return $this->namePartner;
    }

    /**
     * Sets the name of the partner.
     * @param string $namePartner The name to set for the partner.
     * @return static Returns the current instance for method chaining.
     */
    public function setNamePartner(string $namePartner): static
    {
        $this->namePartner = $namePartner;

        return $this;
    }

    /**
     * Returns the image associated with the partner.
     * @return string|null The image of the partner or null if not set.
     */
    public function getImagePartner(): ?string
    {
        return $this->imagePartner;
    }

    /**
     * Sets the image associated with the partner.
     * This is typically a logo or banner image.
     * @param string $imagePartner The image to set for the partner.
     * @return static Returns the current instance for method chaining.
     */
    public function setImagePartner(string $imagePartner): static
    {
        $this->imagePartner = $imagePartner;

        return $this;
    }

    /**
     * Returns the URL associated with the partner.
     * This is typically a link to the partner's website or a relevant page.
     * @return string|null The URL of the partner or null if not set.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Sets the URL associated with the partner.
     * This is used to provide quick access to more information about the partner.
     * @param string $url The URL to set for the partner.
     * @return static Returns the current instance for method chaining.
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Returns the type of the partner.
     * This is a many-to-one relationship where multiple partners can belong to one type.
     * @return PartnerType|null The type of the partner or null if not set.
     */
    public function getTypePartner(): ?PartnerType
    {
        return $this->typePartner;
    }

    /**
     * Sets the type of the partner.
     * This is used to categorize the partner into a specific group (e.g., 'Médias', 'Institutions').
     * @param PartnerType|null $typePartner The type to set for the partner.
     * @return static Returns the current instance for method chaining.
     */
    public function setTypePartner(?PartnerType $typePartner): static
    {
        $this->typePartner = $typePartner;

        return $this;
    }

    /**
     * Returns the date when the partner was last modified.
     * This is used to track changes to the partner's information.
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationPartner(): ?\DateTimeInterface
    {
        return $this->dateModificationPartner;
    }

    /**
     * Sets the date when the partner was last modified.
     * This is used to track changes to the partner's information.
     * @param \DateTimeInterface $dateModificationPartner The date to set for the partner's modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationPartner(\DateTimeInterface $dateModificationPartner): static
    {
        $this->dateModificationPartner = $dateModificationPartner;

        return $this;
    }

    /**
     * Returns the user who last modified the partner's information.
     * This is used for auditing purposes to know who made the last change.
     * @return string|null The username of the user who modified the partner or null if not set.
     */
    public function getUserModificationPartner(): ?string
    {
        return $this->userModificationPartner;
    }

    /**
     * Sets the user who last modified the partner's information.
     * This is used for auditing purposes to know who made the last change.
     * @param string $userModificationPartner The username of the user who modified the partner.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationPartner(string $userModificationPartner): static
    {
        $this->userModificationPartner = $userModificationPartner;

        return $this;
    }

    /**
     * Returns whether the partner is published or not.
     * This is used to control the visibility of the partner in the application.
     * @return bool|null True if the partner is published, false otherwise, or null if not set.
     */
    public function isPublishPartner(): ?bool
    {
        return $this->publishPartner;
    }

    /**
     * Sets whether the partner is published or not.
     * This is used to control the visibility of the partner in the application.
     * @param bool $publishPartner True to publish the partner, false to unpublish it.
     * @return static Returns the current instance for method chaining.
     */
    public function setPublishPartner(bool $publishPartner): static
    {
        $this->publishPartner = $publishPartner;

        return $this;
    }
}

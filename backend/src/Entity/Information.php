<?php

namespace App\Entity;

use App\Repository\InformationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: InformationRepository::class)]
#[ORM\Index(name: 'position_idx', columns: ['position_information'])]
class Information
{
    /**
     * The unique database identifier for the information.
     * This is used to uniquely identify each information entry in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $idInformation = null;

    /**
     * The title of the information, e.g., 'Dates et Lieu', 'Horaires'.
     * This is used to display the title of the information in the application.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $titleInformation = null;

    /**
     * The content of the information, which can include text, links, and other details.
     * This is used to provide detailed information in the application.
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getInformation"])]
    private ?string $contentInformation = null;

    /**
     * The date when the information was last modified.
     * This is used to track changes to the information item and can be useful for auditing purposes.
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationInformation = null;

    /**
     * The user who last modified the information.
     * This is used for auditing purposes to know who made the last change.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationInformation = null;

    /**
     * Indicates whether the information is published or not.
     * This is used to control the visibility of the partner in the application.
     * @var bool|null
     */
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?bool $publishInformation = null;

    /**
     * The section to which this information belongs.
     * This is a many-to-one relationship where multiple information items can belong to one section.
     * @var InformationSection|null
     */
    #[ORM\ManyToOne(inversedBy: 'information')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_information_section')]
    private ?InformationSection $sectionInformation = null;

    /**
     * The position of the information within its section.
     * This is used to order the information items within a section.
     * @var int|null
     */
    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $positionInformation = null;

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of the information section as a string.
     *
     * @return string The name of the information section or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->sectionInformation ?? '';
    }

    /**
     * Returns the unique identifier of the information.
     * @return int|null The ID of the information or null if not set.    
     */
    public function getIdInformation(): ?int
    {
        return $this->idInformation;
    }

    /**
     * Sets the unique identifier for the information.
     * @param int $idInformation The ID to set for the information.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdInformation(int $idInformation): static
    {
        $this->idInformation = $idInformation;

        return $this;
    }

    /**
     * Returns the title of the information.
     * This is used to display the title in the application.
     * @return string|null The title of the information or null if not set.
     */
    public function getTitleInformation(): ?string
    {
        return $this->titleInformation;
    }

    /**
     * Sets the title of the information.
     * This is used to set the title when creating or updating the information.
     * @param string $titleInformation The title to set for the information.
     * @return static Returns the current instance for method chaining.
     */
    public function setTitleInformation(string $titleInformation): static
    {
        $this->titleInformation = $titleInformation;

        return $this;
    }

    /**
     * Returns the content of the information.
     * This is used to provide detailed information in the application.
     * @return string|null The content of the information or null if not set.
     */
    public function getContentInformation(): ?string
    {
        return $this->contentInformation;
    }

    /**
     * Sets the content of the information.
     * This is used to set the content when creating or updating the information.
     * @param string $contentInformation The content to set for the information.
     * @return static Returns the current instance for method chaining.
     */
    public function setContentInformation(string $contentInformation): static
    {
        $this->contentInformation = $contentInformation;

        return $this;
    }

    /**
     * Returns the date when the information was last modified.    
     * This is used to track changes to the information item.
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationInformation(): ?\DateTimeInterface
    {
        return $this->dateModificationInformation;
    }

    /**
     * Sets the date when the information was last modified.
     * This is used to track changes to the information item and can be useful for auditing purposes.
     * @param \DateTimeInterface $dateModificationInformation The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationInformation(\DateTimeInterface $dateModificationInformation): static
    {
        $this->dateModificationInformation = $dateModificationInformation;

        return $this;
    }

    /**
     * Returns the user who last modified the information.
     * This is used for auditing purposes to know who made the last change.
     * @return string|null The username of the user who modified the information or null if not set.
     */
    public function getUserModificationInformation(): ?string
    {
        return $this->userModificationInformation;
    }

    /**
     * Sets the user who last modified the information.
     * This is used for auditing purposes to know who made the last change.
     * @param string $userModificationInformation The username of the user who modified the information.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationInformation(string $userModificationInformation): static
    {
        $this->userModificationInformation = $userModificationInformation;

        return $this;
    }

    /**
     * Returns whether the information is published or not.
     * This is used to control the visibility of the information in the application.
     * @return bool|null True if the information is published, false if not, or null if not set.
     */
    public function isPublishInformation(): ?bool
    {
        return $this->publishInformation;
    }

    /**
     * Sets whether the information is published or not.
     * This is used to control the visibility of the information in the application.
     * @param bool $publishInformation True to publish, false to unpublish.
     * @return static Returns the current instance for method chaining.
     */
    public function setPublishInformation(bool $publishInformation): static
    {
        $this->publishInformation = $publishInformation;

        return $this;
    }

    /**
     * Returns the section to which this information belongs.
     * This is a many-to-one relationship where multiple information items can belong to one section.
     * @return InformationSection|null The section of the information or null if not set.
     */
    public function getSectionInformation(): ?InformationSection
    {
        return $this->sectionInformation;
    }

    /**
     * Sets the section to which this information belongs.
     * This is used to associate the information with a specific section.
     * @param InformationSection|null $sectionInformation The section to set for the information.
     * @return static Returns the current instance for method chaining.
     */
    public function setSectionInformation(?InformationSection $sectionInformation): static
    {
        $this->sectionInformation = $sectionInformation;

        return $this;
    }

    /**
     * Returns the position of the information within its section.
     * This is used to order the information items within a section.
     * @return int|null The position of the information or null if not set.
     */
    public function getPositionInformation(): ?int
    {
        return $this->positionInformation;
    }

    /**
     * Sets the position of the information within its section.
     * This is used to order the information items within a section.
     * @param int $positionInformation The position to set for the information.
     * @return static Returns the current instance for method chaining.
     */
    public function setPositionInformation(int $positionInformation): static
    {
        $this->positionInformation = $positionInformation;

        return $this;
    }
}

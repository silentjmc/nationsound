<?php

namespace App\Entity;

use App\Repository\InformationSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: InformationSectionRepository::class)]
#[ORM\Index(name: 'position_idx', columns: ['position_information_section'])]
class InformationSection
{
    /**
     * The unique database identifier for the information section.
     * This is used to uniquely identify each section in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $idInformationSection = null;

    /**
     * The label of the information section, e.g., 'Festival', 'Scène'.
     * This is used to display the section title in the administration as tag.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $sectionLabel = null;

    /**
     * The title of the information section, e.g., 'Les Scènes'.
     * This is used to display the title of the section in the application.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $titleInformationSection = null;

    /**
     * The content of the information section, which can include text, links, and other details.
     * This is used to provide detailed information in the application.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getInformation"])]
    private ?string $contentInformationSection = null;

    /**
     * The position of the information section, used for sorting.
     * This is used to determine the order in which sections are displayed in the application.
     *
     * @var int|null
     */
    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $positionInformationSection = null;

    /**
     * The date when the information section was last modified.
     * This is used to track changes to the section and can be useful for auditing purposes.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationInformationSection = null;

    /**
     * The user who last modified the information section.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationInformationSection = null;

    /**
     * The collection of information items associated with this section.
     * This is a one-to-many relationship where one section can have multiple information items. 
     * @var Collection<int, Information>
     */
    #[ORM\OneToMany(targetEntity: Information::class, mappedBy: 'sectionInformation')]
    #[Groups(["getInformation"])]
    private Collection $information;

    /**
     * Constructor to initialize the information collection.
     * This is called when a new InformationSection object is created.
     */
    public function __construct()
    {
        $this->information = new ArrayCollection();
    }

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the name of the information labe as a string.
     *
     * @return string The name of the information label or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->sectionLabel ?? '';
    }

    /**
     * Returns the unique identifier of the information section.
     *
     * @return int|null The ID of the information section or null if not set.
     */
    public function getIdInformationSection(): ?int
    {
        return $this->idInformationSection;
    }

    /**
     * Returns the label of the information section.
     * This is used to display the section title in the administration as a tag.
     * @return string|null The label of the information section or null if not set.
     */
    public function getSectionLabel(): ?string
    {
        return $this->sectionLabel;
    }

    /**
     * Sets the label of the information section.
     * This is used to set the section label when creating or updating the section.
     *
     * @param string $sectionLabel The label to set for the information section.
     * @return static Returns the current instance for method chaining.
     */
    public function setSectionLabel(string $sectionLabel): static
    {
        $this->sectionLabel = $sectionLabel;

        return $this;
    }

    /**
     * Returns the title of the information section.
     * This is used to display the title of the section in the application.
     *
     * @return string|null The title of the information section or null if not set.
     */
    public function getTitleInformationSection(): ?string
    {
        return $this->titleInformationSection;
    }

    /**
     * Sets the title of the information section.
     * This is used to set the section title when creating or updating the section.
     *
     * @param string $titleInformationSection The title to set for the information section.
     * @return static Returns the current instance for method chaining.
     */
    public function setTitleInformationSection(string $titleInformationSection): static
    {
        $this->titleInformationSection = $titleInformationSection;

        return $this;
    }

    /**
     * Returns the content of the information section.
     * This can include text, links, and other details.
     *
     * @return string|null The content of the information section or null if not set.
     */
    public function getcontentInformationSection(): ?string
    {
        return $this->contentInformationSection;
    }

    /**
     * Sets the content of the information section.
     * This is used to set the section content when creating or updating the section.
     *
     * @param string $contentInformationSection The content to set for the information section.
     * @return static Returns the current instance for method chaining.
     */
    public function setContentInformationSection(string $contentInformationSection): static
    {
        $this->contentInformationSection = $contentInformationSection;

        return $this;
    }

    /**
     * Returns the position of the information section.
     * This is used for sorting sections in the application.
     *
     * @return int|null The position of the information section or null if not set.
     */
    public function getPositionInformationSection(): ?int
    {
        return $this->positionInformationSection;
    }

    /**
     * Sets the position of the information section.
     * This is used to determine the order in which sections are displayed in the application.
     *
     * @param int $positionInformationSection The position to set for the information section.
     * @return static Returns the current instance for method chaining.
     */
    public function setPositionInformationSection(int $positionInformationSection): static
    {
        $this->positionInformationSection = $positionInformationSection;

        return $this;
    }

    /**
     * Returns the date when the information section was last modified.
     * This is used to track changes to the section and can be useful for auditing purposes.
     *
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationInformationSection(): ?\DateTimeInterface
    {
        return $this->dateModificationInformationSection;
    }

    /**
     * Sets the date when the information section was last modified.
     * This is used to track changes to the section and can be useful for auditing purposes.
     *
     * @param \DateTimeInterface $dateModificationInformationSection The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationInformationSection(\DateTimeInterface $dateModificationInformationSection): static
    {
        $this->dateModificationInformationSection = $dateModificationInformationSection;

        return $this;
    }

    /**
     * Returns the user who last modified the information section.
     * This is used for auditing purposes to know who made the last change.
     *
     * @return string|null The user who last modified the section or null if not set.
     */
    public function getUserModificationInformationSection(): ?string
    {
        return $this->userModificationInformationSection;
    }

    /**
     * Sets the user who last modified the information section.
     * This is used for auditing purposes to know who made the last change.
     *
     * @param string $userModificationInformationSection The username of the user who modified the section.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationInformationSection(string $userModificationInformationSection): static
    {
        $this->userModificationInformationSection = $userModificationInformationSection;

        return $this;
    }

    /**
     * Returns the collection of information items associated with this section.
     * This is a one-to-many relationship where one section can have multiple information items.
     * @return Collection<int, Information>
     */
    public function getInformation(): Collection
    {
        return $this->information;
    }

    /**
     * Adds an information item to the section.
     * This is used to associate an information item with this section.
     *
     * @param Information $information The information item to add to the section.
     * @return static Returns the current instance for method chaining.
     */
    public function addInformation(Information $information): static
    {
        if (!$this->information->contains($information)) {
            $this->information->add($information);
            $information->setSectionInformation($this);
        }

        return $this;
    }

    /**
     * Removes an information item from the section.
     * This is used to disassociate an information item from this section.
     *
     * @param Information $information The information item to remove from the section.
     * @return static Returns the current instance for method chaining.
     */
    public function removeInformation(Information $information): static
    {
        if ($this->information->removeElement($information)) {
            // set the owning side to null (unless already changed)
            if ($information->getSectionInformation() === $this) {
                $information->setSectionInformation(null);
            }
        }

        return $this;
    }
}
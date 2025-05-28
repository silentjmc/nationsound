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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $idInformationSection = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $sectionLabel = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $titleInformationSection = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getInformation"])]
    private ?string $contentInformationSection = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $positionInformationSection = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationInformationSection = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationInformationSection = null;

    /**
     * @var Collection<int, Information>
     */
    #[ORM\OneToMany(targetEntity: Information::class, mappedBy: 'sectionInformation')]
    #[Groups(["getInformation"])]
    private Collection $information;

    public function __construct()
    {
        $this->information = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->sectionLabel ?? '';
    }


    public function getIdInformationSection(): ?int
    {
        return $this->idInformationSection;
    }

    public function getSectionLabel(): ?string
    {
        return $this->sectionLabel;
    }

    public function setSectionLabel(string $sectionLabel): static
    {
        $this->sectionLabel = $sectionLabel;

        return $this;
    }

    public function getTitleInformationSection(): ?string
    {
        return $this->titleInformationSection;
    }

    public function setTitleInformationSection(string $titleInformationSection): static
    {
        $this->titleInformationSection = $titleInformationSection;

        return $this;
    }

    public function getcontentInformationSection(): ?string
    {
        return $this->contentInformationSection;
    }

    public function setContentInformationSection(string $contentInformationSection): static
    {
        $this->contentInformationSection = $contentInformationSection;

        return $this;
    }

    public function getPositionInformationSection(): ?int
    {
        return $this->positionInformationSection;
    }

    public function setPositionInformationSection(int $positionInformationSection): static
    {
        $this->positionInformationSection = $positionInformationSection;

        return $this;
    }

    public function getDateModificationInformationSection(): ?\DateTimeInterface
    {
        return $this->dateModificationInformationSection;
    }

    public function setDateModificationInformationSection(\DateTimeInterface $dateModificationInformationSection): static
    {
        $this->dateModificationInformationSection = $dateModificationInformationSection;

        return $this;
    }

    public function getUserModificationInformationSection(): ?string
    {
        return $this->userModificationInformationSection;
    }

    public function setUserModificationInformationSection(string $userModificationInformationSection): static
    {
        $this->userModificationInformationSection = $userModificationInformationSection;

        return $this;
    }

    /**
     * @return Collection<int, Information>
     */
    public function getInformation(): Collection
    {
        return $this->information;
    }

    public function addInformation(Information $information): static
    {
        if (!$this->information->contains($information)) {
            $this->information->add($information);
            $information->setSectionInformation($this);
        }

        return $this;
    }

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

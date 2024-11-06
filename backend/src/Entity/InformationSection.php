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
#[ORM\Index(name: 'position_idx', columns: ['position'])]
class InformationSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $section = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getInformation"])]
    private ?string $description = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $position = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(length: 255)]
    private ?string $userModification = null;

    /**
     * @var Collection<int, Information>
     */
    #[ORM\OneToMany(targetEntity: Information::class, mappedBy: 'typeSection')]
    #[Groups(["getInformation"])]
    private Collection $information;

    public function __construct()
    {
        $this->information = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->section ?? '';
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

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(string $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getUserModification(): ?string
    {
        return $this->userModification;
    }

    public function setUserModification(string $userModification): static
    {
        $this->userModification = $userModification;

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
            $information->setTypeSection($this);
        }

        return $this;
    }

    public function removeInformation(Information $information): static
    {
        if ($this->information->removeElement($information)) {
            // set the owning side to null (unless already changed)
            if ($information->getTypeSection() === $this) {
                $information->setTypeSection(null);
            }
        }

        return $this;
    }
}

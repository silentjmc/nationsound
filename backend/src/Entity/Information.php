<?php

namespace App\Entity;

use App\Repository\InformationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: InformationRepository::class)]
#[ORM\Index(name: 'position_idx', columns: ['position'])]
class Information
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getInformation"])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(length: 255)]
    private ?string $userModification = null;

    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?bool $publish = null;

    #[ORM\ManyToOne(inversedBy: 'information')]
    #[ORM\JoinColumn(nullable: false)]
    private ?InformationSection $typeSection = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $position = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function isPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): static
    {
        $this->publish = $publish;

        return $this;
    }

    public function getTypeSection(): ?InformationSection
    {
        return $this->typeSection;
    }

    public function setTypeSection(?InformationSection $typeSection): static
    {
        $this->typeSection = $typeSection;

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
}

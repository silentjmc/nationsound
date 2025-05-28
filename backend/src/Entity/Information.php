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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $idInformation = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getInformation"])]
    private ?string $titleInformation = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getInformation"])]
    private ?string $contentInformation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationInformation = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationInformation = null;

    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?bool $publishInformation = null;

    #[ORM\ManyToOne(inversedBy: 'information')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_information_section')]
    private ?InformationSection $sectionInformation = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getInformation"])]
    private ?int $positionInformation = null;

    public function __toString(): string
    {
        return $this->sectionInformation ?? '';
    }

    public function getIdInformation(): ?int
    {
        return $this->idInformation;
    }

    public function setIdInformation(int $idInformation): static
    {
        $this->idInformation = $idInformation;

        return $this;
    }

    public function getTitleInformation(): ?string
    {
        return $this->titleInformation;
    }

    public function setTitleInformation(string $titleInformation): static
    {
        $this->titleInformation = $titleInformation;

        return $this;
    }

    public function getContentInformation(): ?string
    {
        return $this->contentInformation;
    }

    public function setContentInformation(string $contentInformation): static
    {
        $this->contentInformation = $contentInformation;

        return $this;
    }

    public function getDateModificationInformation(): ?\DateTimeInterface
    {
        return $this->dateModificationInformation;
    }

    public function setDateModificationInformation(\DateTimeInterface $dateModificationInformation): static
    {
        $this->dateModificationInformation = $dateModificationInformation;

        return $this;
    }

    public function getUserModificationInformation(): ?string
    {
        return $this->userModificationInformation;
    }

    public function setUserModificationInformation(string $userModificationInformation): static
    {
        $this->userModificationInformation = $userModificationInformation;

        return $this;
    }

    public function isPublishInformation(): ?bool
    {
        return $this->publishInformation;
    }

    public function setPublishInformation(bool $publishInformation): static
    {
        $this->publishInformation = $publishInformation;

        return $this;
    }

    public function getSectionInformation(): ?InformationSection
    {
        return $this->sectionInformation;
    }

    public function setSectionInformation(?InformationSection $sectionInformation): static
    {
        $this->sectionInformation = $sectionInformation;

        return $this;
    }

    public function getPositionInformation(): ?int
    {
        return $this->positionInformation;
    }

    public function setPositionInformation(int $positionInformation): static
    {
        $this->positionInformation = $positionInformation;

        return $this;
    }
}

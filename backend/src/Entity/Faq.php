<?php

namespace App\Entity;

use App\Repository\FaqRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
//use Gedmo\Sortable\Entity\Repository\SortableRepository;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
//#[ORM\Entity(repositoryClass: SortableRepository::class)]
#[ORM\Index(name: 'position_idx', columns: ['position'])]
//#[ORM\Table(name: 'faq')]
class Faq
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?int $id = null;

    //#[Gedmo\SortableGroup]
    #[ORM\Column(length: 255)]
    #[Groups(["getFaq"])]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getFaq"])]
    private ?string $reponse = null;

    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?bool $publish = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(length: 255)]
    private ?string $userModification = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getFaq"])]
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

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(string $reponse): static
    {
        $this->reponse = $reponse;

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

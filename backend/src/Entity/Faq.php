<?php

namespace App\Entity;

use App\Repository\FaqRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
#[ORM\Index(name: 'position_idx', columns: ['position_faq'])]
class Faq
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?int $idFaq = null;

    //#[Gedmo\SortableGroup]
    #[ORM\Column(length: 255)]
    #[Groups(["getFaq"])]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getFaq"])]
    private ?string $reponse = null;

    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?bool $publishFaq = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationFaq = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationFaq = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?int $positionFaq = null;

    public function __toString(): string
    {
        return $this->idFaq ?? '';
    }

    public function getIdFaq(): ?int
    {
        return $this->idFaq;
    }

    public function setIdFaq(int $idFaq): static
    {
        $this->idFaq = $idFaq;

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

    public function isPublishFaq(): ?bool
    {
        return $this->publishFaq;
    }

    public function setPublishFaq(bool $publishFaq): static
    {
        $this->publishFaq = $publishFaq;

        return $this;
    }

    public function getDateModificationFaq(): ?\DateTimeInterface
    {
        return $this->dateModificationFaq;
    }

    public function setDateModificationFaq(\DateTimeInterface $dateModificationFaq): static
    {
        $this->dateModificationFaq = $dateModificationFaq;

        return $this;
    }

    public function getUserModificationFaq(): ?string
    {
        return $this->userModificationFaq;
    }

    public function setUserModificationFaq(string $userModificationFaq): static
    {
        $this->userModificationFaq = $userModificationFaq;

        return $this;
    }

    public function getPositionFaq(): ?int
    {
        return $this->positionFaq;
    }

    public function setPositionFaq(int $positionFaq): static
    {
        $this->positionFaq = $positionFaq;

        return $this;
    }
}

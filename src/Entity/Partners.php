<?php

namespace App\Entity;

use App\Repository\PartnersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartnersRepository::class)]
class Partners
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'partners')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPartners"])]
    private ?PartnerType $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(length: 255)]
    private ?string $userModification = null;

    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?bool $publish = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?PartnerType
    {
        return $this->type;
    }

    public function setType(?PartnerType $type): static
    {
        $this->type = $type;

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
}

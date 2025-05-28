<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?int $idPartner = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $namePartner = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $imagePartner = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartners"])]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: PartnerType::class, inversedBy: 'partner')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_partner_type',name: 'type_partner_id',)]
    #[Groups(["getPartners"])]
    private ?PartnerType $typePartner = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationPartner = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationPartner = null;

    #[ORM\Column]
    #[Groups(["getPartners"])]
    private ?bool $publishPartner = null;

    public function __toString(): string
    {
        return $this->name_partner ?? '';
    }

    public function getIdPartner(): ?int
    {
        return $this->idPartner;
    }

    public function setIdPartner(int $idPartner): static
    {
        $this->idPartner = $idPartner;

        return $this;
    }

    public function getNamePartner(): ?string
    {
        return $this->namePartner;
    }

    public function setNamePartner(string $namePartner): static
    {
        $this->namePartner = $namePartner;

        return $this;
    }

    public function getImagePartner(): ?string
    {
        return $this->imagePartner;
    }

    public function setImagePartner(string $imagePartner): static
    {
        $this->imagePartner = $imagePartner;

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

    public function getTypePartner(): ?PartnerType
    {
        return $this->typePartner;
    }

    public function setTypePartner(?PartnerType $typePartner): static
    {
        $this->typePartner = $typePartner;

        return $this;
    }

    public function getDateModificationPartner(): ?\DateTimeInterface
    {
        return $this->dateModificationPartner;
    }

    public function setDateModificationPartner(\DateTimeInterface $dateModificationPartner): static
    {
        $this->dateModificationPartner = $dateModificationPartner;

        return $this;
    }

    public function getUserModificationPartner(): ?string
    {
        return $this->userModificationPartner;
    }

    public function setUserModificationPartner(string $userModificationPartner): static
    {
        $this->userModificationPartner = $userModificationPartner;

        return $this;
    }

    public function isPublishPartner(): ?bool
    {
        return $this->publishPartner;
    }

    public function setPublishPartner(bool $publishPartner): static
    {
        $this->publishPartner = $publishPartner;

        return $this;
    }
}

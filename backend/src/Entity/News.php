<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getNews"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getNews"])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getNews"])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(["getNews"])]
    private ?bool $publish = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["getNews"])]
    private ?bool $push = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
     private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(length: 255)]
    private ?string $userModification = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getNews"])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(["getNews"])]
    private ?\DateTimeInterface $notificationEndDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getNews"])]
    private ?\DateTimeInterface $notificationDate = null;

    
    public function __toString(): string
    {
        return $this->notificationEndDate instanceof DateTime ? $this->notificationEndDate->format('d/m/Y') : '';
    }

    public function getDateToString(): string
    {
        return $this->__toString();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function isPush(): ?bool
    {
        return $this->push;
    }
/*
    public function setPush(bool $push): static
    {
        $this->push = $push;

        return $this;
    }*/
    public function setPush(bool $push): self
    {
        // Si on active la notification, on met à jour la date
        if ($push) {
            $this->setNotificationDate(new \DateTime());
        } else {
            // Si on désactive la notification, on retire la date
            $this->setNotificationDate(null);
        }
        
        $this->push = $push;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getNotificationEndDate(): ?\DateTimeInterface
    {
        return $this->notificationEndDate;
    }

    public function setNotificationEndDate(?\DateTimeInterface $notificationEndDate): static
    {
        $this->notificationEndDate = $notificationEndDate;

        return $this;
    }

    public function getNotificationDate(): ?\DateTimeInterface
    {
        return $this->notificationDate;
    }

    public function setNotificationDate(?\DateTimeInterface $notificationDate): static
    {
        $this->notificationDate = $notificationDate;

        return $this;
    }
}

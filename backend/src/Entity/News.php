<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getNews"])]
    private ?int $idNews = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getNews"])]
    private ?string $titleNews = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getNews"])]
    private ?string $contentNews = null;

    #[ORM\Column]
    #[Groups(["getNews"])]
    private ?bool $publishNews = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["getNews"])]
    private ?bool $push = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
     private ?\DateTimeInterface $dateModificationNews = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationNews = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getNews"])]
    private ?string $typeNews = null;

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

    public function getIdNews(): ?int
    {
        return $this->idNews;
    }

    public function getTitleNews(): ?string
    {
        return $this->titleNews;
    }

    public function setTitleNews(string $titleNews): static
    {
        $this->titleNews = $titleNews;

        return $this;
    }

    public function getContentNews(): ?string
    {
        return $this->contentNews;
    }

    public function setContentNews(string $contentNews): static
    {
        $this->contentNews = $contentNews;

        return $this;
    }

    public function isPublishNews(): ?bool
    {
        return $this->publishNews;
    }

    public function setPublishNews(bool $publishNews): static
    {
        $this->publishNews = $publishNews;
        // If the news is unpublished, we also disable the push notification
        if (!$publishNews) {
            $this->setPush(false);
        }
        return $this;
    }

    public function isPush(): ?bool
    {
        return $this->push;
    }

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

    public function getDateModificationNews(): ?\DateTimeInterface
    {
        return $this->dateModificationNews;
    }

    public function setDateModificationNews(\DateTimeInterface $dateModificationNews): static
    {
        $this->dateModificationNews = $dateModificationNews;

        return $this;
    }

    public function getUserModificationNews(): ?string
    {
        return $this->userModificationNews;
    }

    public function setUserModificationNews(string $userModificationNews): static
    {
        $this->userModificationNews = $userModificationNews;

        return $this;
    }

    public function getTypeNews(): ?string
    {
        return $this->typeNews;
    }
    public function setTypeNews(string $typeNews): static
    {
        $this->typeNews = $typeNews;

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
    
    #[Assert\Callback]
    public function validatePushNotification(ExecutionContextInterface $context, $payload)
    {
        if ($this->isPush() && !$this->isPublishNews()) {
            // link the error to the 'push' field in the form
            $context->buildViolation('Une actualité ne peut pas être notifiée si elle n\'est pas publiée.')
                ->atPath('push') 
                ->addViolation();
        }
    }
}
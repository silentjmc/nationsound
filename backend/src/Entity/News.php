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
    /**
     * The unique database identifier for the news item.
     * This is used to uniquely identify each news item in the database.
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getNews"])]
    private ?int $idNews = null;

    /**
     * The title of the news item, e.g., 'Intempérie'.
     * This is used to display the title of the news item in the application.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getNews"])]
    private ?string $titleNews = null;

    /**
     * The content of the news item, typically a detailed description or announcement.
     * This is used to provide more information about the news item in the application.
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getNews"])]
    private ?string $contentNews = null;

    /**
     * Indicates whether the news item is published.
     * If true, the news item is visible to users; if false, it is not.
     * This is used to control the visibility of the news item in the application.
     * @var bool|null
     */
    #[ORM\Column]
    #[Groups(["getNews"])]
    private ?bool $publishNews = null;

    /**
     * Indicates whether a push notification should be sent for this news item.
     * If true, a notification will be sent; if false, it will not.
     * This is used to manage notifications for news items.
     * @var bool|null
     */
    #[ORM\Column(options: ["default" => false])]
    #[Groups(["getNews"])]
    private ?bool $push = false;

    /**
     * The date when the news item was last modified.
     * This is used to track changes to the news item and can be useful for auditing purposes.
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
     private ?\DateTimeInterface $dateModificationNews = null;

    /**
     * The user who last modified the news item.
     * This is used for auditing purposes to know who made the last change.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationNews = null;

    /**
     * The type of the news item, which can be 'Normal', 'Important', or 'Urgent'.
     * This is used to categorize the news item and can be useful for filtering or sorting.
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getNews"])]
    private ?string $typeNews = null;

    /**
     * The end date for the notification of the news item.
     * This is used to determine when the notification should stop being displayed in the application.
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(["getNews"])]
    private ?\DateTimeInterface $notificationEndDate = null;

    /**
     * The date when the push notification was sent.
     * This is used to track when the notification was last sent and can be useful for managing notifications.
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getNews"])]
    private ?\DateTimeInterface $notificationDate = null;

    /**
     * This method is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the formatted notification end date as a string.
     * @return string The formatted notification end date or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->notificationEndDate instanceof DateTime ? $this->notificationEndDate->format('d/m/Y') : '';
    }

    /**
     * Returns the formatted notification end date as a string.
     * This is used to provide a human-readable representation of the notification end date.
     * @return string The formatted notification end date or an empty string if not set.
     */
    public function getDateToString(): string
    {
        return $this->__toString();
    }

    /**
     * Returns the unique identifier of the news item.
     * This is used to uniquely identify each news item in the database.
     * @return int|null The ID of the news item or null if not set.
     */
    public function getIdNews(): ?int
    {
        return $this->idNews;
    }

    /**
     * Sets the unique identifier for the news item.
     * This is used to uniquely identify each news item in the database.
     * @param int $idNews The ID to set for the news item.
     * @return static Returns the current instance for method chaining.
     */
    public function getTitleNews(): ?string
    {
        return $this->titleNews;
    }

    /**
     * Sets the title of the news item.
     * This is used to display the title of the news item in the application.
     * @param string $titleNews The title to set for the news item.
     * @return static Returns the current instance for method chaining.
     */
    public function setTitleNews(string $titleNews): static
    {
        $this->titleNews = $titleNews;

        return $this;
    }

    /**
     * Returns the content of the news item.
     * This is used to provide more information about the news item in the application.
     * @return string|null The content of the news item or null if not set.
     */
    public function getContentNews(): ?string
    {
        return $this->contentNews;
    }

    /**
     * Sets the content of the news item.
     * This is used to provide more information about the news item in the application.
     * @param string $contentNews The content to set for the news item.
     * @return static Returns the current instance for method chaining.
     */
    public function setContentNews(string $contentNews): static
    {
        $this->contentNews = $contentNews;

        return $this;
    }

    /**
     * Returns whether the news item is published.
     * If true, the news item is visible to users; if false, it is not.
     * This is used to control the visibility of the news item in the application.
     * @return bool|null True if published, false if not, or null if not set.
     */
    public function isPublishNews(): ?bool
    {
        return $this->publishNews;
    }

    /**
     * Sets whether the news item is published.
     * If true, the news item will be visible to users; if false, it will not.
     * This is used to control the visibility of the news item in the application.
     * @param bool $publishNews True to publish, false to unpublish.
     * @return static Returns the current instance for method chaining.
     */
    public function setPublishNews(bool $publishNews): static
    {
        $this->publishNews = $publishNews;
        // If the news is unpublished, we also disable the push notification
        if (!$publishNews) {
            $this->setPush(false);
        }
        return $this;
    }

    /**
     * Returns whether a push notification should be sent for this news item.
     * If true, a notification will be sent; if false, it will not.
     * This is used to manage notifications for news items.
     * @return bool|null True if a push notification is enabled, false if not, or null if not set.
     */
    public function isPush(): ?bool
    {
        return $this->push;
    }

    /**
     * Sets whether a push notification should be sent for this news item.
     * If true, a notification will be sent; if false, it will not.
     * This is used to manage notifications for news items.
     * If the push notification is enabled, the notification date is set to the current date.
     * If it is disabled, the notification date is set to null.
     * @param bool $push True to enable push notification, false to disable it.
     * @return static Returns the current instance for method chaining.
     */
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

    /**
     * Returns the date when the news item was last modified.
     * This is used to track changes to the news item and can be useful for auditing purposes.
     * @return \DateTimeInterface|null The modification date or null if not set.
     */
    public function getDateModificationNews(): ?\DateTimeInterface
    {
        return $this->dateModificationNews;
    }

    /**
     * Sets the date when the news item was last modified.
     * This is used to track changes to the news item and can be useful for auditing purposes.
     * @param \DateTimeInterface $dateModificationNews The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationNews(\DateTimeInterface $dateModificationNews): static
    {
        $this->dateModificationNews = $dateModificationNews;

        return $this;
    }

    /**
     * Returns the user who last modified the news item.
     * This is used for auditing purposes to know who made the last change.
     * @return string|null The username of the user who last modified the news item or null if not set.
     */
    public function getUserModificationNews(): ?string
    {
        return $this->userModificationNews;
    }

    /**
     * Sets the user who last modified the news item.
     * This is used for auditing purposes to know who made the last change.
     * @param string $userModificationNews The username of the user who last modified the news item.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationNews(string $userModificationNews): static
    {
        $this->userModificationNews = $userModificationNews;

        return $this;
    }

    /**
     * Returns the type of the news item, which can be 'Normal', 'Important', or 'Urgent'.
     * This is used to categorize the news item and can be useful for filtering or sorting.
     * @return string|null The type of the news item or null if not set.
     */
    public function getTypeNews(): ?string
    {
        return $this->typeNews;
    }

    /**
     * Sets the type of the news item, which can be 'Normal', 'Important', or 'Urgent'.
     * This is used to categorize the news item and can be useful for filtering or sorting.
     * @param string $typeNews The type to set for the news item.
     * @return static Returns the current instance for method chaining.
     */
    public function setTypeNews(string $typeNews): static
    {
        $this->typeNews = $typeNews;

        return $this;
    }

    /**
     * Returns the end date for the notification of the news item.
     * This is used to determine when the notification should stop being displayed in the application.
     * @return \DateTimeInterface|null The end date for the notification or null if not set.
     */
    public function getNotificationEndDate(): ?\DateTimeInterface
    {
        return $this->notificationEndDate;
    }

    /**
     * Sets the end date for the notification of the news item.
     * This is used to determine when the notification should stop being displayed in the application.
     * @param \DateTimeInterface|null $notificationEndDate The end date to set for the notification.
     * @return static Returns the current instance for method chaining.
     */
    public function setNotificationEndDate(?\DateTimeInterface $notificationEndDate): static
    {
        $this->notificationEndDate = $notificationEndDate;

        return $this;
    }

    /**
     * Returns the date when the push notification was sent.
     * This is used to track when the notification was last sent and can be useful for managing notifications.
     * @return \DateTimeInterface|null The date of the push notification or null if not set.
     */
    public function getNotificationDate(): ?\DateTimeInterface
    {
        return $this->notificationDate;
    }

    /**
     * Sets the date when the push notification was sent.
     * This is used to track when the notification was last sent and can be useful for managing notifications.
     * @param \DateTimeInterface|null $notificationDate The date to set for the push notification.
     * @return static Returns the current instance for method chaining.
     */
    public function setNotificationDate(?\DateTimeInterface $notificationDate): static
    {
        $this->notificationDate = $notificationDate;

        return $this;
    }
    
    /**
     * Validates that a push notification can only be sent if the news item is published.
     * This is used to ensure that only published news items can trigger push notifications.
     * 
     * @Assert\IsTrue(message="Une actualité ne peut pas être notifiée si elle n'est pas publiée.")
     */
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
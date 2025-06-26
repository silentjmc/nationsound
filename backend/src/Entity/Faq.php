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
    /**
     * The unique database identifier for the FAQ entry.
     * This is used to uniquely identify each FAQ in the database.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?int $idFaq = null;

    /**
     * The question of the FAQ entry, e.g., 'Quelle est la date du festival ?'.
     * This is used to display the question in the application.
     *
     * @var string|null
     */
    //#[Gedmo\SortableGroup]
    #[ORM\Column(length: 255)]
    #[Groups(["getFaq"])]
    private ?string $question = null;

    /**
     * The answer to the FAQ entry.
     * This is used to provide the answer in the application.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getFaq"])]
    private ?string $reponse = null;

    /**
     * Indicates whether the FAQ entry is published or not.
     * This is used to control the visibility of the FAQ in the application.
     *
     * @var bool|null
     */
    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?bool $publishFaq = null;

    /**
     * The date when the FAQ entry was last modified.
     * This is used to track changes to the FAQ item and can be useful for auditing purposes.
     *
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationFaq = null;

    /**
     * The user who last modified the FAQ entry.
     * This is used for auditing purposes to know who made the last change.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $userModificationFaq = null;

    /**
     * The position of the FAQ entry, used for sorting.
     * This is used to determine the order in which FAQs are displayed.
     *
     * @var int|null
     */
    #[Gedmo\SortablePosition]
    #[ORM\Column]
    #[Groups(["getFaq"])]
    private ?int $positionFaq = null;

    /**
     * This is often used by Symfony forms or EasyAdmin when displaying the entity in a list or dropdown.
     * It returns the id of Faq as a string.
     *
     * @return string The id of Faq or an empty string if not set.
     */
    public function __toString(): string
    {
        return $this->idFaq ?? '';
    }

    /**
     * Returns the unique identifier of the FAQ entry.
     * @return int|null The unique identifier of the FAQ entry or null if not set.
     */
    public function getIdFaq(): ?int
    {
        return $this->idFaq;
    }

    /**
     * Sets the unique identifier of the FAQ entry.
     * @param int $idFaq The unique identifier to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setIdFaq(int $idFaq): static
    {
        $this->idFaq = $idFaq;

        return $this;
    }

    /**
     * Returns the question of the FAQ entry.
     * @return string|null The question of the FAQ entry or null if not set.
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * Sets the question of the FAQ entry.
     * This is used to display the question in the application.
     * @param string $question The question to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Returns the answer to the FAQ entry.
     * This is used to provide the answer in the application.
     * @return string|null The answer to the FAQ entry or null if not set.
     */
    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    /**
     * Sets the answer to the FAQ entry.
     * This is used to provide the answer in the application.
     * @param string $reponse The answer to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setReponse(string $reponse): static
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Returns whether the FAQ entry is published or not.
     * This is used to control the visibility of the FAQ in the application.
     * @return bool|null True if published, false if not, or null if not set.
     */
    public function isPublishFaq(): ?bool
    {
        return $this->publishFaq;
    }

    /**
     * Sets whether the FAQ entry is published or not.
     * This is used to control the visibility of the FAQ in the application.
     * @param bool $publishFaq True to publish, false to unpublish.
     * @return static Returns the current instance for method chaining.
     */
    public function setPublishFaq(bool $publishFaq): static
    {
        $this->publishFaq = $publishFaq;

        return $this;
    }

    /**
     * Returns the date when the FAQ entry was last modified.
     * This is used to track changes to the FAQ item and can be useful for auditing purposes.
     * @return \DateTimeInterface|null The date of modification or null if not set.
     */
    public function getDateModificationFaq(): ?\DateTimeInterface
    {
        return $this->dateModificationFaq;
    }

    /**
     * Sets the date when the FAQ entry was last modified.
     * This is used to track changes to the FAQ item and can be useful for auditing purposes.
     * @param \DateTimeInterface $dateModificationFaq The date to set for the last modification.
     * @return static Returns the current instance for method chaining.
     */
    public function setDateModificationFaq(\DateTimeInterface $dateModificationFaq): static
    {
        $this->dateModificationFaq = $dateModificationFaq;

        return $this;
    }

    /**
     * Returns the user who last modified the FAQ entry.
     * This is used for auditing purposes to know who made the last change.
     * @return string|null The username of the user who modified the FAQ or null if not set.
     */
    public function getUserModificationFaq(): ?string
    {
        return $this->userModificationFaq;
    }

    /**
     * Sets the user who last modified the FAQ entry.
     * This is used for auditing purposes to know who made the last change.
     * @param string $userModificationFaq The username of the user who modified the FAQ.
     * @return static Returns the current instance for method chaining.
     */
    public function setUserModificationFaq(string $userModificationFaq): static
    {
        $this->userModificationFaq = $userModificationFaq;

        return $this;
    }

    /**
     * Returns the position of the FAQ entry, used for sorting.
     * This is used to determine the order in which FAQs are displayed.
     * @return int|null The position of the FAQ entry or null if not set.
     */
    public function getPositionFaq(): ?int
    {
        return $this->positionFaq;
    }

    /**
     * Sets the position of the FAQ entry, used for sorting.
     * This is used to determine the order in which FAQs are displayed.
     * @param int $positionFaq The position to set for the FAQ entry.
     * @return static Returns the current instance for method chaining.
     */
    public function setPositionFaq(int $positionFaq): static
    {
        $this->positionFaq = $positionFaq;

        return $this;
    }
}
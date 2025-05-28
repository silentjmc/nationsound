<?php

namespace App\Entity;

use App\Repository\EventLocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventLocationRepository::class)]
class EventLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEventLocation"])]
    private ?int $idEventLocation = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEventLocation", "getEvent","getArtist"])]
    private ?string $nameEventLocation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 14)]
    #[Groups(["getEventLocation"])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 14)]
    #[Groups(["getEventLocation"])]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getEventLocation"])]
    private ?string $contentEventLocation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModificationEventLocation = null;

    #[ORM\Column(length: 255)]
    private ?string $userModificationEventLocation = null;

    #[ORM\Column]
    private ?bool $publishEventLocation = null;

    #[ORM\ManyToOne(inversedBy: 'eventLocations')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_location_type')]
    #[Groups(["getEventLocation"])]
    private ?LocationType $typeLocation = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'eventLocation')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nameEventLocation ?? '';
    }

    public function getIdEventLocation(): ?int
    {
        return $this->idEventLocation;
    }

    public function setIdEventLocation(int $idEventLocation): static
    {
        $this->idEventLocation = $idEventLocation;
        return $this;
    }

    public function getNameEventLocation(): ?string
    {
        return $this->nameEventLocation;
    }

    public function setNameEventLocation(string $nameEventLocation): static
    {
        $this->nameEventLocation = $nameEventLocation;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getContentEventLocation(): ?string
    {
        return $this->contentEventLocation;
    }

    public function setContentEventLocation(string $contentEventLocation): static
    {
        $this->contentEventLocation = $contentEventLocation;

        return $this;
    }

    public function getDateModificationEventLocation(): ?\DateTimeInterface
    {
        return $this->dateModificationEventLocation;
    }

    public function setDateModificationEventLocation(\DateTimeInterface $dateModificationEventLocation): static
    {
        $this->dateModificationEventLocation = $dateModificationEventLocation;

        return $this;
    }

    public function getUserModificationEventLocation(): ?string
    {
        return $this->userModificationEventLocation;
    }

    public function setUserModificationEventLocation(string $userModificationEventLocation): static
    {
        $this->userModificationEventLocation = $userModificationEventLocation;

        return $this;
    }

    public function isPublishEventLocation(): ?bool
    {
        return $this->publishEventLocation;
    }

    public function setPublishEventLocation(bool $publishEventLocation): static
    {
        $this->publishEventLocation = $publishEventLocation;

        return $this;
    }

    public function getTypeLocation(): ?LocationType
    {
        return $this->typeLocation;
    }

    public function setTypeLocation(?LocationType $typeLocation): static
    {
        $this->typeLocation = $typeLocation;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setEventLocation($this);
        }
        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getEventLocation() === $this) {
                $event->setEventLocation(null);
            }
        }
        return $this;
    }


    #[Assert\Callback]
     public function validateUnpublish(ExecutionContextInterface $context, $payload): void
    {

        if (!$this->isPublishEventLocation()) { // Si l'état actuel (soumis) est "non publié"
            $hasRelatedPublishedEvents = false;
            foreach ($this->getEvents() as $event) {
                if ($event->isPublishEvent()) { // Assurez-vous que Event a une méthode isPublishEvent()
                    $hasRelatedPublishedEvents = true;
                    break;
                }
            }

            if ($hasRelatedPublishedEvents) {
                $context->buildViolation(sprintf(
                    'Le lieu "%s" ne peut pas être dépublié directement depuis ce formulaire car il a des événements publiés liés. Veuillez utiliser l\'action "Dépublier" depuis la liste des lieux, qui gérera la dépublication des événements associés.',
                    $this->getNameEventLocation() ?? 'ce lieu' // Utilisez le nom du lieu si disponible
                ))
                ->atPath('publishEventLocation') // Lie l'erreur au champ "Publié"
                ->addViolation();
            }
        }
    }
}
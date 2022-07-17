<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AlnFeederRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [],
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary' => 'Retrieve feeder status and settings',
                'description' => 'Retrieve feeder status and settings',
            ],
        ],
        'put' => [
            'openapi_context' => [
                'summary' => 'Update feeder name',
                'description' => 'Update feeder name',
            ],
        ],
    ],
    shortName: 'Feeder',
    denormalizationContext: ['groups' => ['feeder:input']],
    normalizationContext: ['groups' => ['feeder:output']],
    validationGroups: ['feeder:validation'],
)]
#[ORM\Entity(repositoryClass: AlnFeederRepository::class)]
class AlnFeeder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(['feeder:output'])]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    #[Groups(['feeder:output'])]
    private string $identifier;

    #[ORM\Column(length: 255)]
    #[Groups(['feeder:input', 'feeder:output'])]
    #[Assert\Length(max: 255, groups: ['feeder:validation'])]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['feeder:output'])]
    private \DateTimeImmutable $lastSeen;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['feeder:output'])]
    private ?int $defaultMealAmount = null;

    /**
     * @var Collection<int, AlnPlanning>
     */
    #[ORM\OneToMany(mappedBy: 'feeder', targetEntity: AlnPlanning::class, orphanRemoval: true)]
    private Collection $plannings;

    /**
     * @var Collection<int, AlnMeal>
     */
    #[ORM\OneToMany(mappedBy: 'feeder', targetEntity: AlnMeal::class, orphanRemoval: true)]
    private Collection $meals;

    public function __construct()
    {
        $this->identifier = '';
        $this->name = '';
        $this->lastSeen = new DateTimeImmutable();
        $this->meals = new ArrayCollection();
        $this->plannings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastSeen(): \DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function setLastSeen(\DateTimeImmutable $lastSeen): self
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    public function getDefaultMealAmount(): ?int
    {
        return $this->defaultMealAmount;
    }

    public function setDefaultMealAmount(?int $defaultMealAmount): self
    {
        $this->defaultMealAmount = $defaultMealAmount;

        return $this;
    }

    /**
     * @return Collection<int, AlnPlanning>
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(AlnPlanning $planning): self
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings[] = $planning;
            $planning->setFeeder($this);
        }

        return $this;
    }

    public function removePlanning(AlnPlanning $planning): self
    {
        if ($this->plannings->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getFeeder() === $this) {
                $planning->setFeeder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AlnMeal>
     */
    public function getMeals(): Collection
    {
        return $this->meals;
    }

    public function addMeal(AlnMeal $meal): self
    {
        if (!$this->meals->contains($meal)) {
            $this->meals[] = $meal;
            $meal->setFeeder($this);
        }

        return $this;
    }

    public function removeMeal(AlnMeal $meal): self
    {
        if ($this->meals->removeElement($meal)) {
            // set the owning side to null (unless already changed)
            if ($meal->getFeeder() === $this) {
                $meal->setFeeder(null);
            }
        }

        return $this;
    }
}

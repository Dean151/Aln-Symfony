<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\Dto\PlanningInput;
use App\Controller\ChangeDefaultMealController;
use App\Controller\ChangePlanningController;
use App\Controller\FeedNowController;
use App\Repository\AlnFeederRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
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
            'security' => "is_granted('view', object)",
        ],
        'put' => [
            'openapi_context' => [
                'summary' => 'Update feeder name',
                'description' => 'Update feeder name',
            ],
            'security' => "is_granted('manage', object)",
        ],
        'feed' => [
            'method' => 'POST',
            'status' => Response::HTTP_OK,
            'path' => '/feeders/{id}/feed',
            'controller' => FeedNowController::class,
            'denormalization_context' => ['groups' => ['feeding:input']],
            'validation_groups' => ['feeding:validation'],
            'openapi_context' => [
                'summary' => 'Trigger a meal immediately with specified amount in grams',
                'description' => 'Trigger a meal immediately with specified amount in grams',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Meal distributed',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Feeder not registered',
                    ],
                    Response::HTTP_CONFLICT => [
                        'description' => 'Feeder not connected',
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY => [
                        'description' => 'Meal amount is not valid',
                    ],
                    Response::HTTP_SERVICE_UNAVAILABLE => [
                        'description' => 'Feeder did not responded to request',
                    ],
                ],
            ],
        ],
        'amount' => [
            'method' => 'PUT',
            'status' => Response::HTTP_OK,
            'path' => '/feeders/{id}/amount',
            'controller' => ChangeDefaultMealController::class,
            'denormalization_context' => ['groups' => ['feeding:input']],
            'validation_groups' => ['feeding:validation'],
            'openapi_context' => [
                'summary' => 'Update the amount distributed when the feeder button is pressed in grams',
                'description' => 'Update the amount distributed when the feeder button is pressed in grams',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Default meal amount updated',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Feeder not registered',
                    ],
                    Response::HTTP_CONFLICT => [
                        'description' => 'Feeder not connected',
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY => [
                        'description' => 'Meal amount is not valid',
                    ],
                    Response::HTTP_SERVICE_UNAVAILABLE => [
                        'description' => 'Feeder did not responded to request',
                    ],
                ],
            ],
        ],
        'planning' => [
            'method' => 'PUT',
            'status' => Response::HTTP_OK,
            'path' => '/feeders/{id}/planning',
            'controller' => ChangePlanningController::class,
            'input' => PlanningInput::class,
            'denormalization_context' => ['groups' => []],
            'validation_groups' => [],
            'openapi_context' => [
                'summary' => 'Replace the meal plan with a new one',
                'description' => 'Replace the meal plan with a new one',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Planning replaced',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Feeder not registered',
                    ],
                    Response::HTTP_CONFLICT => [
                        'description' => 'Feeder not connected',
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY => [
                        'description' => 'Meal plan is not valid',
                    ],
                    Response::HTTP_SERVICE_UNAVAILABLE => [
                        'description' => 'Feeder did not responded to request',
                    ],
                ],
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

    #[ApiProperty(required: true, example: 'Newton')]
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

    #[ApiProperty(required: true, example: 5)]
    #[Groups(['feeding:input'])]
    #[Assert\Range(min: 5, max: 150, groups: ['feeding:validation'])]
    public int $amount; // DTO used for feeding ; and changing default meal amount

    public ?PlanningInput $planning = null; // DTO used for change planning

    public function __construct()
    {
        $this->identifier = '';
        $this->name = '';
        $this->lastSeen = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
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

    #[Groups(['feeder:output'])]
    public function isAvailable(): bool
    {
        $now = new DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return ($now->getTimestamp() - $this->lastSeen->getTimestamp()) <= 30;
    }

    #[Groups(['feeder:output'])]
    public function getCurrentPlanning(): ?AlnPlanning
    {
        return $this->plannings->last() ?: null;
    }
}

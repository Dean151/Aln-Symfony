<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ApiResource\Dto\IdentifierInput;
use App\ApiResource\Dto\PlanningInput;
use App\ApiResource\Provider\AlnFeederProvider;
use App\Controller\AssociateFeeder;
use App\Controller\ChangeDefaultMeal;
use App\Controller\ChangePlanning;
use App\Controller\DissociateFeeder;
use App\Controller\TriggerManualMeal;
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
    shortName: 'Feeder',
    operations: [
        new Post(
            uriTemplate: '/feeders/associate',
            status: Response::HTTP_OK,
            controller: AssociateFeeder::class,
            openapiContext: [
                'summary' => 'Associate an unassociated feeder to your account',
                'description' => 'Associate an unassociated feeder to your account',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'identifier' => ['type' => 'string', 'example' => 'ALE123456789'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Feeder associated',
                    ],
                    Response::HTTP_UNAUTHORIZED => [
                        'description' => 'Not logged in',
                    ],
                    Response::HTTP_FORBIDDEN => [
                        'description' => 'Feeder already associated',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Feeder not registered',
                    ],
                ],
            ],
            denormalizationContext: ['groups' => []],
            security: 'is_granted(\'IS_AUTHENTICATED\')',
            validationContext: ['groups' => []],
            input: IdentifierInput::class,
        ),
        new Get(
            openapiContext: [
                'summary' => 'Retrieve feeder status and settings',
                'description' => 'Retrieve feeder status and settings',
            ],
            security: 'is_granted(\'VIEW\', object)',
        ),
        new Put(
            openapiContext: [
                'summary' => 'Update feeder name',
                'description' => 'Update feeder name',
            ],
            security: 'is_granted(\'MANAGE\', object)',
        ),
        new Delete(
            uriTemplate: '/feeders/{id}/association',
            status: Response::HTTP_OK,
            controller: DissociateFeeder::class,
            openapiContext: [
                'summary' => 'Dissociate an associated feeder from your account',
                'description' => 'Dissociate an associated feeder from your account',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Feeder dissociated',
                    ],
                    Response::HTTP_UNAUTHORIZED => [
                        'description' => 'Not logged in',
                    ],
                    Response::HTTP_FORBIDDEN => [
                        'description' => 'Feeder not associated to current account',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Feeder not registered',
                    ],
                ],
            ],
            denormalizationContext: ['groups' => []],
            security: 'is_granted(\'MANAGE\', object)',
            validationContext: ['groups' => []]
        ),
        new Post(
            uriTemplate: '/feeders/{id}/feed',
            status: Response::HTTP_OK,
            controller: TriggerManualMeal::class,
            openapiContext: [
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
            denormalizationContext: ['groups' => ['feeding:input']],
            security: 'is_granted(\'MANAGE\', object)',
            validationContext: ['groups' => ['feeding:validation']],
            provider: AlnFeederProvider::class,
        ),
        new Put(
            uriTemplate: '/feeders/{id}/amount',
            status: Response::HTTP_OK,
            controller: ChangeDefaultMeal::class,
            openapiContext: [
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
            denormalizationContext: ['groups' => ['feeding:input']],
            security: 'is_granted(\'MANAGE\', object)',
            validationContext: ['groups' => ['feeding:validation']],
        ),
        new Put(
            uriTemplate: '/feeders/{id}/planning',
            status: Response::HTTP_OK,
            controller: ChangePlanning::class,
            openapiContext: [
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
            denormalizationContext: ['groups' => ['planning:input']],
            security: 'is_granted(\'MANAGE\', object)',
            input: PlanningInput::class,
        ),
    ],
    normalizationContext: ['groups' => ['feeder:output']],
    denormalizationContext: ['groups' => ['feeder:input']],
    validationContext: ['groups' => ['feeder:validation']],
)]
#[ORM\Entity(repositoryClass: AlnFeederRepository::class)]
class AlnFeeder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['feeder:output'])]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    #[Groups(['feeder:output'])]
    private string $identifier;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(length: 255)]
    #[Groups(['feeder:input', 'feeder:output'])]
    #[Assert\Length(max: 255, groups: ['feeder:validation'])]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'feeders')]
    private ?User $owner = null;

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
     * @var Collection<int, AlnManualMeal>
     */
    #[ORM\OneToMany(mappedBy: 'feeder', targetEntity: AlnManualMeal::class, orphanRemoval: true)]
    private Collection $manualMeals;

    #[ApiProperty(required: true, example: '5')]
    #[Groups(['feeding:input'])]
    #[Assert\Range(min: 5, max: 150, groups: ['feeding:validation'])]
    public int $amount; // DTO used for feeding ; and changing default meal amount

    public function __construct()
    {
        $this->identifier = '';
        $this->name = '';
        $this->lastSeen = new DateTimeImmutable('now');
        $this->manualMeals = new ArrayCollection();
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

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

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
     * @return Collection<int, AlnManualMeal>
     */
    public function getManualMeals(): Collection
    {
        return $this->manualMeals;
    }

    public function addManualMeal(AlnManualMeal $meal): self
    {
        if (!$this->manualMeals->contains($meal)) {
            $this->manualMeals[] = $meal;
            $meal->setFeeder($this);
        }

        return $this;
    }

    public function removeManualMeal(AlnManualMeal $meal): self
    {
        if ($this->manualMeals->removeElement($meal)) {
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
        $now = new DateTimeImmutable('now');

        return ($now->getTimestamp() - $this->lastSeen->getTimestamp()) <= 30;
    }

    #[Groups(['feeder:output'])]
    public function getCurrentPlanning(): ?AlnPlanning
    {
        return $this->plannings->last() ?: null;
    }
}

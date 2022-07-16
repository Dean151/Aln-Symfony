<?php

namespace App\Entity;

use App\Repository\AlnFeederRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;

#[ORM\Entity(repositoryClass: AlnFeederRepository::class)]
class AlnFeeder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    private string $identifier;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 64)]
    private string $ip;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $lastSeen;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
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
        $this->ip = '';
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

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

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

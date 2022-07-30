<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AlnPlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AlnPlanningRepository::class)]
class AlnPlanning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AlnFeeder $feeder = null;

    /**
     * @var Collection<int, AlnMeal>
     */
    #[ORM\OneToMany(mappedBy: 'planning', targetEntity: AlnMeal::class)]
    #[Groups(['feeder:output'])]
    private Collection $meals;

    #[ORM\Column]
    private \DateTimeImmutable $createdOn;

    public function __construct()
    {
        $this->meals = new ArrayCollection();
        $this->createdOn = new DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeeder(): ?AlnFeeder
    {
        return $this->feeder;
    }

    public function setFeeder(?AlnFeeder $feeder): self
    {
        $this->feeder = $feeder;

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
            $meal->setPlanning($this);
        }

        return $this;
    }

    public function removeMeal(AlnMeal $meal): self
    {
        if ($this->meals->removeElement($meal)) {
            // set the owning side to null (unless already changed)
            if ($meal->getPlanning() === $this) {
                $meal->setPlanning(null);
            }
        }

        return $this;
    }

    public function getCreatedOn(): \DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function setCreatedOn(\DateTimeImmutable $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }
}

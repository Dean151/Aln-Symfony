<?php

declare(strict_types=1);

namespace App\Entity;

use App\Dbal\Types\AlnTimeType;
use App\Repository\AlnManualMealRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlnManualMealRepository::class)]
class AlnManualMeal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'manualMeals')]
    #[ORM\JoinColumn(nullable: false)]
    private AlnFeeder $feeder;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $distributedOn = null;

    /**
     * @var int<5, 150>
     */
    #[ORM\Column(type: Types::SMALLINT)]
    /* @phpstan-ignore doctrine.columnType */
    private int $amount;

    /**
     * @var ?array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    #[ORM\Column(type: AlnTimeType::ALN_TIME_TYPE, nullable: true)]
    private ?array $previousMeal = null;

    public function __construct()
    {
        $this->amount = 5;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeeder(): AlnFeeder
    {
        return $this->feeder;
    }

    public function setFeeder(AlnFeeder $feeder): self
    {
        $this->feeder = $feeder;

        return $this;
    }

    public function getDistributedOn(): ?\DateTimeImmutable
    {
        return $this->distributedOn;
    }

    public function setDistributedOn(?\DateTimeImmutable $distributedOn): self
    {
        $this->distributedOn = $distributedOn;

        return $this;
    }

    /**
     * @return int<5, 150>
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int<5, 150> $amount
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return ?array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    public function getPreviousMeal(): ?array
    {
        return $this->previousMeal;
    }

    /**
     * @param ?array{hours: int<0, 23>, minutes: int<0, 59>} $previousMeal
     */
    public function setPreviousMeal(?array $previousMeal): self
    {
        $this->previousMeal = $previousMeal;

        return $this;
    }
}

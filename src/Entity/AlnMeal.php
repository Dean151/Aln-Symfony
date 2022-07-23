<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Types\AlnTimeType;
use App\Repository\AlnMealRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: AlnMealRepository::class)]
class AlnMeal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'meals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AlnFeeder $feeder = null;

    #[ORM\ManyToOne(inversedBy: 'meals')]
    private ?AlnPlanning $planning = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $distributedOn = null;

    /**
     * @var ?array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    #[ORM\Column(type: AlnTimeType::ALN_TIME_TYPE, nullable: true)]
    #[Groups(['feeder:output'])]
    private ?array $time = null;

    /**
     * @var int<5, 150>
     */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['feeder:output'])]
    private int $amount;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[SerializedName('enabled')]
    #[Groups(['feeder:output'])]
    private bool $isEnabled;

    public function __construct()
    {
        $this->amount = 5;
        $this->isEnabled = true;
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

    public function getPlanning(): ?AlnPlanning
    {
        return $this->planning;
    }

    public function setPlanning(?AlnPlanning $planning): self
    {
        $this->planning = $planning;

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
     * @return ?array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    public function getTime(): ?array
    {
        return $this->time;
    }

    /**
     * @param ?array{hours: int<0, 23>, minutes: int<0, 59>} $time
     */
    public function setTime(?array $time): self
    {
        $this->time = $time;

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

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}

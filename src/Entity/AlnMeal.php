<?php

namespace App\Entity;

use App\Api\Dto\TimeInput;
use App\Repository\AlnMealRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;

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

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private \DateTimeImmutable $time;

    /**
     * @var int<5, 150>
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private int $amount;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isEnabled;

    public function __construct()
    {
        $this->time = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
    }

    public function setTime(\DateTimeImmutable $time): self
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

    public function isIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}

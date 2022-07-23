<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Types\AlnTimeType;
use App\Repository\AlnAlertRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;

#[ORM\Entity(repositoryClass: AlnAlertRepository::class)]
class AlnAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeImmutable $triggeredOn;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne]
    private ?AlnFeeder $feeder = null;

    /**
     * @var ?array{hours: int<0, 23>, minutes: int<0, 59>}
     */
    #[ORM\Column(type: AlnTimeType::ALN_TIME_TYPE, nullable: true)]
    private ?array $time = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $amount = null;

    public function __construct()
    {
        $this->type = 'unknown';
        $this->triggeredOn = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTriggeredOn(): \DateTimeImmutable
    {
        return $this->triggeredOn;
    }

    public function setTriggeredOn(\DateTimeImmutable $triggeredOn): self
    {
        $this->triggeredOn = $triggeredOn;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}

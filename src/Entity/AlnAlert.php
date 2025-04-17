<?php

declare(strict_types=1);

namespace App\Entity;

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

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $triggeredOn;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne]
    private ?AlnFeeder $feeder = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ip = null;

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

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}

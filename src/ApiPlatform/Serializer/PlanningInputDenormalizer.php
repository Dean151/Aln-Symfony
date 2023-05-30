<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer;

use App\ApiPlatform\Dto\PlanningInput;
use App\Entity\AlnFeeder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class PlanningInputDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if (!is_array($data) || PlanningInput::class !== $type || !($context[AbstractNormalizer::OBJECT_TO_POPULATE] instanceof AlnFeeder)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, ?bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [PlanningInput::class => true];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): PlanningInput
    {
        $planning = $this->normalizer->denormalize($data, PlanningInput::class, $format, $context);
        assert($context[AbstractNormalizer::OBJECT_TO_POPULATE] instanceof AlnFeeder);
        $planning->feeder = $context[AbstractNormalizer::OBJECT_TO_POPULATE];

        return $planning;
    }
}

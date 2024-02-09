<?php

declare(strict_types=1);

namespace App\ApiResource\Serializer;

use App\ApiResource\Dto\PlanningInput;
use App\Entity\AlnFeeder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PlanningInputDenormalizer implements DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
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
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): PlanningInput
    {
        $planning = $this->denormalizer->denormalize($data, PlanningInput::class, $format, $context);
        assert($context[AbstractNormalizer::OBJECT_TO_POPULATE] instanceof AlnFeeder);
        $planning->feeder = $context[AbstractNormalizer::OBJECT_TO_POPULATE];

        return $planning;
    }
}

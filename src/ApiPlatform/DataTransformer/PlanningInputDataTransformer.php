<?php

declare(strict_types=1);

namespace App\ApiPlatform\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\ApiPlatform\Dto\PlanningInput;
use App\Entity\AlnFeeder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class PlanningInputDataTransformer implements DataTransformerInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param PlanningInput        $object
     * @param array<string, mixed> $context
     */
    public function transform($object, string $to, array $context = []): AlnFeeder
    {
        $this->validator->validate($object);

        $feeder = $context[AbstractNormalizer::OBJECT_TO_POPULATE];
        assert($feeder instanceof AlnFeeder);

        $feeder->planning = $object;

        return $feeder;
    }

    /**
     * @param mixed                $data
     * @param array<string, mixed> $context
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof AlnFeeder) {
            return false;
        }

        return AlnFeeder::class === $to && PlanningInput::class === ($context['input']['class'] ?? null);
    }
}

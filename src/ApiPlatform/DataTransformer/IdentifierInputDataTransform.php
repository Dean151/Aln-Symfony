<?php

declare(strict_types=1);

namespace App\ApiPlatform\DataTransformer;

use ApiPlatform\Validator\ValidatorInterface;
use App\ApiPlatform\Dto\IdentifierInput;
use App\Entity\AlnFeeder;
use App\Repository\AlnFeederRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @deprecated Use of DataTransformerInterface
 */
class IdentifierInputDataTransform implements \ApiPlatform\Core\DataTransformer\DataTransformerInterface
{
    private ValidatorInterface $validator;
    private AlnFeederRepository $repository;

    public function __construct(ValidatorInterface $validator, AlnFeederRepository $repository)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @param IdentifierInput      $object
     * @param array<string, mixed> $context
     */
    public function transform($object, string $to, array $context = []): AlnFeeder
    {
        $this->validator->validate($object);

        $feeder = $this->repository->findOneByIdentifier($object->identifier);
        if (null === $feeder) {
            throw new NotFoundHttpException('Not found');
        }

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

        return AlnFeeder::class === $to && IdentifierInput::class === ($context['input']['class'] ?? null);
    }
}

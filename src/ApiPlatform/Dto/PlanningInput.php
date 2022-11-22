<?php

declare(strict_types=1);

namespace App\ApiPlatform\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Put;
use App\ApiPlatform\Provider\FeederProvider;
use App\Controller\ChangePlanning;
use App\Entity\AlnFeeder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Put(
            uriTemplate: '/feeders/{id}/planning',
            uriVariables: [
                'id' => new Link(fromProperty: 'feeder', fromClass: AlnFeeder::class),
            ],
            status: Response::HTTP_OK,
            controller: ChangePlanning::class,
            openapiContext: [
                'summary' => 'Replace the meal plan with a new one',
                'description' => 'Replace the meal plan with a new one',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Planning replaced',
                    ],
                    Response::HTTP_NOT_FOUND => [
                        'description' => 'Feeder not registered',
                    ],
                    Response::HTTP_CONFLICT => [
                        'description' => 'Feeder not connected',
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY => [
                        'description' => 'Meal plan is not valid',
                    ],
                    Response::HTTP_SERVICE_UNAVAILABLE => [
                        'description' => 'Feeder did not responded to request',
                    ],
                ],
            ],
            denormalizationContext: ['groups' => ['planning:input']],
            security: 'is_granted(\'MANAGE\', object)',
            provider: FeederProvider::class
        ),
    ],
)]
final class PlanningInput
{
    public AlnFeeder $feeder;

    /**
     * @var MealInput[]
     */
    #[Groups('planning:input')]
    #[Assert\Valid]
    public array $meals;
}

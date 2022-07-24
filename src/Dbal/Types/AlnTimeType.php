<?php

declare(strict_types=1);

namespace App\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

final class AlnTimeType extends StringType
{
    public const ALN_TIME_TYPE = 'aln_time';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 5;

        return parent::getSQLDeclaration($column, $platform);
    }

    /**
     * @param ?string $value
     *
     * @return ?array{hours: int<0, 23>, minutes: int<0, 59>}
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if (is_null($value)) {
            return null;
        }
        $components = explode(':', $value);
        $hours = (int) $components[0];
        $minutes = (int) $components[1];
        if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
            throw new ConversionException('Out of bounds time');
        }

        return ['hours' => $hours, 'minutes' => $minutes];
    }

    /**
     * @param ?array{hours: int<0, 23>, minutes: int<0, 59>} $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }
        $hours = str_pad((string) $value['hours'], 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string) $value['minutes'], 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}";
    }

    public function getName(): string
    {
        return self::ALN_TIME_TYPE;
    }
}

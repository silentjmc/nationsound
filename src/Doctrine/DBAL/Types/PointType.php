<?php

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PointType extends Type
{
    const POINT = 'point';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'POINT';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }

        $point = unpack('x/x/x/x/corder/Ltype/dx/dy', $value);
        return [
            'x' => $point['x'],
            'y' => $point['y']
        ];
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        if ($value === null) {
            return null;
        }

        return pack('xxxxcLdd', 1, 1, $value['x'], $value['y']);
    }

    public function getName()
    {
        return self::POINT;
    }
}
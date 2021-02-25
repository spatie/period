<?php

namespace Spatie\Period\Exceptions;

use Exception;
use Spatie\Period\Precision;

class CannotCeilLowerPrecision extends Exception
{
    public static function precisionIsLower(Precision $a, Precision $b): CannotCeilLowerPrecision
    {
        $from = self::unitName($a);
        $to = self::unitName($b);

        return new self("Cannot get the latest $from of a $to.");
    }

    protected static function unitName(Precision $precision)
    {
        return match ($precision->intervalName()) {
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        };
    }
}

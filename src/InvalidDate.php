<?php

namespace Spatie\Period;

use InvalidArgumentException;

class InvalidDate extends InvalidArgumentException
{
    public static function forFormat(
        string $date,
        string $format
    ): InvalidDate {
        return new self("Could not construct a date from {$date} with format {$format}");
    }
}

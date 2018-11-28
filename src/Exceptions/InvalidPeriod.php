<?php

namespace Spatie\Period\Exceptions;

use DateTimeImmutable;
use InvalidArgumentException;

class InvalidPeriod extends InvalidArgumentException
{
    public static function endBeforeStart(DateTimeImmutable $start, DateTimeImmutable $end): InvalidPeriod
    {
        return new static("The end time `{$end}` is before the start time `{$start}`.");
    }
}

<?php

namespace Spatie\Period\Exceptions;

use DateTimeImmutable;
use InvalidArgumentException;

class InvalidPeriod extends InvalidArgumentException
{
    public static function endBeforeStart(DateTimeImmutable $start, DateTimeImmutable $end): InvalidPeriod
    {
        return new static("The end time `{$end->format('Y-m-d H:i:s')}` is before the start time `{$start->format('Y-m-d H:i:s')}`.");
    }
}

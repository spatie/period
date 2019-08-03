<?php

namespace Spatie\Period\Exceptions;

use DateTimeInterface;
use InvalidArgumentException;

class InvalidPeriod extends InvalidArgumentException
{
    public static function endBeforeStart(DateTimeInterface $start, DateTimeInterface $end): InvalidPeriod
    {
        return new static("The end time `{$end->format('Y-m-d H:i:s')}` is before the start time `{$start->format('Y-m-d H:i:s')}`.");
    }
}

<?php

namespace Spatie\Period;

use DateTimeImmutable;
use InvalidArgumentException;

class InvalidPeriod extends InvalidArgumentException
{
    public static function endBeforeStart(
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ): InvalidPeriod {
        return new self("The end time ({$end}) is before the start time ({$start})");
    }
}

<?php

declare(strict_types=1);

namespace Spatie\Period;

use DateTimeImmutable;

final class PeriodDuration
{
    /** @var Period */
    private $period;

    public function __construct(Period $period)
    {
        $this->period = $period;
    }

    public function equals(PeriodDuration $other): bool
    {
        return $this->startAndEndDatesAreTheSameAs($other)
            || $this->includedStartAndEndDatesAreTheSameAs($other)
            || $this->numberOfDaysIsTheSameAs($other)
            || $this->compareTo($other) === 0;
    }

    public function isLargerThan(PeriodDuration $other): bool
    {
        return $this->compareTo($other) === 1;
    }

    public function isSmallerThan(PeriodDuration $other): bool
    {
        return $this->compareTo($other) === -1;
    }

    public function compareTo(PeriodDuration $other): int
    {
        $now = new DateTimeImmutable('@'.time()); // Ensure a TimeZone independent instance

        $here = $this->period->getIncludedEnd()->diff($this->period->getIncludedStart(), true);
        $there = $other->period->getIncludedEnd()->diff($other->period->getIncludedStart(), true);

        return $now->add($here)->getTimestamp() <=> $now->add($there)->getTimestamp();
    }

    private function startAndEndDatesAreTheSameAs(PeriodDuration $other): bool
    {
        return $this->period->getStart() == $other->period->getStart()
            && $this->period->getEnd() == $other->period->getEnd();
    }

    private function includedStartAndEndDatesAreTheSameAs(PeriodDuration $other): bool
    {
        return $this->period->getIncludedStart() == $other->period->getIncludedStart()
            && $this->period->getIncludedEnd() == $other->period->getIncludedEnd();
    }

    private function numberOfDaysIsTheSameAs(PeriodDuration $other)
    {
        $here = $this->period->getIncludedEnd()->diff($this->period->getIncludedStart(), true);
        $there = $other->period->getIncludedEnd()->diff($other->period->getIncludedStart(), true);

        return $here->format('%a') === $there->format('%a');
    }
}

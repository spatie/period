<?php

declare(strict_types=1);

namespace Spatie\Period;

use DateTimeImmutable;

class PeriodDuration
{
    public function __construct(
        private Period $period
    ) {
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
        $now = new DateTimeImmutable('@' . time()); // Ensure a TimeZone independent instance

        $here = $this->period->includedEnd()->diff($this->period->includedStart(), true);
        $there = $other->period->includedEnd()->diff($other->period->includedStart(), true);

        return $now->add($here)->getTimestamp() <=> $now->add($there)->getTimestamp();
    }

    private function startAndEndDatesAreTheSameAs(PeriodDuration $other): bool
    {
        return $this->period->start() == $other->period->start()
            && $this->period->end() == $other->period->end();
    }

    private function includedStartAndEndDatesAreTheSameAs(PeriodDuration $other): bool
    {
        return $this->period->includedStart() == $other->period->includedStart()
            && $this->period->includedEnd() == $other->period->includedEnd();
    }

    private function numberOfDaysIsTheSameAs(PeriodDuration $other)
    {
        $here = $this->period->includedEnd()->diff($this->period->includedStart(), true);
        $there = $other->period->includedEnd()->diff($other->period->includedStart(), true);

        return $here->format('%a') === $there->format('%a');
    }
}

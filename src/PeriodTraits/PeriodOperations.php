<?php

namespace Spatie\Period\PeriodTraits;

use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\PeriodFactory;

/** @mixin Period */
trait PeriodOperations
{
    public function gap(Period $period): ?static
    {
        $this->ensurePrecisionMatches($period);

        if ($this->overlapsWith($period)) {
            return null;
        }

        if ($this->touchesWith($period)) {
            return null;
        }

        if ($this->includedStart() >= $period->includedEnd()) {
            return static::make(
                $period->includedEnd()->add($this->interval),
                $this->includedStart()->sub($this->interval),
                $this->precision()
            );
        }

        return static::make(
            $this->includedEnd()->add($this->interval),
            $period->includedStart()->sub($this->interval),
            $this->precision()
        );
    }

    public function overlap(Period ...$others): ?static
    {
        if (count($others) === 0) {
            return null;
        } elseif (count($others) > 1) {
            return $this->overlapAll(...$others);
        } else {
            $other = $others[0];
        }

        $this->ensurePrecisionMatches($other);

        $includedStart = $this->includedStart() > $other->includedStart()
            ? $this->includedStart()
            : $other->includedStart();

        $includedEnd = $this->includedEnd() < $other->includedEnd()
            ? $this->includedEnd()
            : $other->includedEnd();

        if ($includedStart > $includedEnd) {
            return null;
        }

        return PeriodFactory::makeWithBoundaries(
            static::class,
            $includedStart,
            $includedEnd,
            $this->precision(),
            $this->boundaries(),
        );
    }

    protected function overlapAll(Period ...$periods): ?static
    {
        $overlap = clone $this;

        if (! count($periods)) {
            return $overlap;
        }

        foreach ($periods as $period) {
            $overlap = $overlap->overlap($period);

            if ($overlap === null) {
                return null;
            }
        }

        return $overlap;
    }

    /**
     * @param \Spatie\Period\Period ...$others
     *
     * @return \Spatie\Period\PeriodCollection|static[]
     */
    public function overlapAny(Period ...$others): PeriodCollection
    {
        $overlapCollection = new PeriodCollection();

        foreach ($others as $period) {
            $overlap = $this->overlap($period);

            if ($overlap === null) {
                continue;
            }

            $overlapCollection[] = $overlap;
        }

        return $overlapCollection;
    }

    /**
     * @param \Spatie\Period\Period|iterable $other
     *
     * @return \Spatie\Period\PeriodCollection|static[]
     */
    public function subtract(Period ...$others): PeriodCollection
    {
        if (count($others) === 0) {
            return PeriodCollection::make($this);
        } elseif (count($others) > 1) {
            return $this->subtractAll(...$others);
        } else {
            $other = $others[0];
        }

        $this->ensurePrecisionMatches($other);

        $collection = new PeriodCollection();

        if (! $this->overlapsWith($other)) {
            $collection[] = $this;

            return $collection;
        }

        if ($this->includedStart() < $other->includedStart()) {
            $collection[] = PeriodFactory::makeWithBoundaries(
                static::class,
                $this->includedStart(),
                $other->includedStart()->sub($this->interval),
                $this->precision(),
                $this->boundaries(),
            );
        }

        if ($this->includedEnd() > $other->includedEnd()) {
            $collection[] = PeriodFactory::makeWithBoundaries(
                static::class,
                $other->includedEnd()->add($this->interval),
                $this->includedEnd(),
                $this->precision(),
                $this->boundaries(),
            );
        }

        return $collection;
    }

    protected function subtractAll(Period ...$others): PeriodCollection
    {
        $timezone = $this->includedStart()->getTimezone()->getName();
        $empty = [];
        $nonEmpty = [];

        foreach ([$this, ...$others] as $key => $period) {
            $isEmpty = $period->includedStart() > $period->includedEnd();

            if (
                ($isEmpty && $key === 0)
                || $period->includedStart()->getTimezone()->getName() !== $timezone
                || $period->includedEnd()->getTimezone()->getName() !== $timezone
            ) {
                // Preserve existing fragments and calendar arithmetic outside the sweep's assumptions.
                $subtractions = array_map(fn (Period $other) => $this->subtract($other), $others);

                return (new PeriodCollection($this))->overlapAll(...$subtractions);
            }

            if ($isEmpty) {
                $empty[] = $period;
            } elseif ($key !== 0) {
                $nonEmpty[] = $period;
            }
        }

        $others = (new PeriodCollection(...$nonEmpty))->sort();

        $collection = new PeriodCollection();
        $start = $this->includedStart();

        foreach ($others as $other) {
            $this->ensurePrecisionMatches($other);

            if ($other->includedStart() > $start) {
                $end = min($this->precision->decrement($other->includedStart()), $this->includedEnd());

                if ($start <= $end) {
                    $collection[] = PeriodFactory::makeWithBoundaries(
                        static::class,
                        $start,
                        $end,
                        $this->precision(),
                        $this->boundaries(),
                    );
                }
            }

            $start = max($start, $this->precision->increment($other->includedEnd()));
        }

        if ($start <= $this->includedEnd()) {
            $collection[] = PeriodFactory::makeWithBoundaries(
                static::class,
                $start,
                $this->includedEnd(),
                $this->precision(),
                $this->boundaries(),
            );
        }

        // Periods without included dates split the result as subtracting them one by one does
        $subtractions = array_map(fn (Period $other) => $this->subtract($other), $empty);

        return $collection->overlapAll(...$subtractions);
    }

    /**
     * @param \Spatie\Period\Period $other
     *
     * @return \Spatie\Period\PeriodCollection|static[]
     */
    public function diffSymmetric(Period $other): PeriodCollection
    {
        $this->ensurePrecisionMatches($other);

        $periodCollection = new PeriodCollection();

        if (! $this->overlapsWith($other)) {
            $periodCollection[] = clone $this;
            $periodCollection[] = clone $other;

            return $periodCollection;
        }

        $boundaries = (new PeriodCollection($this, $other))->boundaries();

        $overlap = $this->overlap($other);

        return $boundaries->subtract($overlap);
    }

    public function renew(): static
    {
        $length = $this->includedStart->diff($this->includedEnd);

        $start = $this->includedEnd->add($this->interval);

        $end = $start->add($length);

        return static::make($start, $end, $this->precision, $this->boundaries);
    }
}

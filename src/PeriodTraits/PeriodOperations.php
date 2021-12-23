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
        $subtractions = [];

        foreach ($others as $other) {
            $subtractions[] = $this->subtract($other);
        }

        return (new PeriodCollection($this))->overlapAll(...$subtractions);
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

<?php

namespace Spatie\Period;

use Closure;
use Iterator;
use Countable;
use ArrayAccess;

class PeriodCollection implements ArrayAccess, Iterator, Countable
{
    use IterableImplementation;

    /** @var \Spatie\Period\Period[] */
    protected $periods;

    public function __construct(Period ...$periods)
    {
        $this->periods = $periods;
    }

    public function current(): Period
    {
        return $this->periods[$this->position];
    }

    public function overlapSingle(PeriodCollection $periodCollection): PeriodCollection
    {
        $overlaps = new PeriodCollection();

        foreach ($this as $period) {
            foreach ($periodCollection as $otherPeriod) {
                if (! $period->overlapSingle($otherPeriod)) {
                    continue;
                }

                $overlaps[] = $period->overlapSingle($otherPeriod);
            }
        }

        return $overlaps;
    }

    public function overlap(PeriodCollection ...$periodCollections): PeriodCollection
    {
        $overlap = clone $this;

        foreach ($periodCollections as $periodCollection) {
            $overlap = $overlap->overlapSingle($periodCollection);
        }

        return $overlap;
    }

    public function boundaries(): ?Period
    {
        $start = null;
        $end = null;

        foreach ($this as $period) {
            if ($start === null || $start > $period->getIncludedStart()) {
                $start = $period->getStart();
            }

            if ($end === null || $end < $period->getIncludedEnd()) {
                $end = $period->getEnd();
            }
        }

        if (! $start || ! $end) {
            return null;
        }

        [$firstPeriod] = $this->periods;

        return new Period(
            $start,
            $end,
            $firstPeriod->getPrecisionMask(),
            Boundaries::EXCLUDE_NONE
        );
    }

    public function gaps(): PeriodCollection
    {
        $boundaries = $this->boundaries();

        if (! $boundaries) {
            return new PeriodCollection();
        }

        return $boundaries->diff(...$this);
    }

    public function intersect(Period $intersection): PeriodCollection
    {
        $intersected = new PeriodCollection();

        foreach ($this as $period) {
            $overlap = $intersection->overlapSingle($period);

            if ($overlap === null) {
                continue;
            }

            $intersected[] = $overlap;
        }

        return $intersected;
    }

    public function add(Period ...$periods): PeriodCollection
    {
        $collection = clone $this;

        foreach ($periods as $period) {
            $collection[] = $period;
        }

        return $collection;
    }

    public function map(Closure $closure): PeriodCollection
    {
        $collection = clone $this;

        foreach ($collection->periods as $key => $period) {
            $collection->periods[$key] = $closure($period);
        }

        return $collection;
    }

    public function reduce(Closure $closure, $initial = null)
    {
        $carry = $initial;

        foreach ($this as $period) {
            $carry = $closure($carry, $period);
        }

        return $carry;
    }

    public function isEmpty(): bool
    {
        return count($this->periods) === 0;
    }
}

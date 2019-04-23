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

    /**
     * @param \Spatie\Period\Period ...$periods
     *
     * @return static
     */
    public static function make(Period ...$periods): PeriodCollection
    {
        return new static(...$periods);
    }

    public function __construct(Period ...$periods)
    {
        $this->periods = $periods;
    }

    public function current(): Period
    {
        return $this->periods[$this->position];
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

    /**
     * @return static
     */
    public function gaps(): PeriodCollection
    {
        $boundaries = $this->boundaries();

        if (! $boundaries) {
            return static::make();
        }

        return $boundaries->diff(...$this);
    }

    /**
     * @param \Spatie\Period\Period $intersection
     *
     * @return static
     */
    public function intersect(Period $intersection): PeriodCollection
    {
        $intersected = static::make();

        foreach ($this as $period) {
            $overlap = $intersection->overlapSingle($period);

            if ($overlap === null) {
                continue;
            }

            $intersected[] = $overlap;
        }

        return $intersected;
    }

    /**
     * @param \Spatie\Period\Period ...$periods
     *
     * @return static
     */
    public function add(Period ...$periods): PeriodCollection
    {
        $collection = clone $this;

        foreach ($periods as $period) {
            $collection[] = $period;
        }

        return $collection;
    }

    /**
     * @param \Closure $closure
     *
     * @return static
     */
    public function map(Closure $closure): PeriodCollection
    {
        $collection = clone $this;

        foreach ($collection->periods as $key => $period) {
            $collection->periods[$key] = $closure($period);
        }

        return $collection;
    }

    /**
     * @param \Closure $closure
     * @param mixed $initial
     *
     * @return mixed|null
     */
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

    private function overlapSingle(PeriodCollection $periodCollection): PeriodCollection
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
}

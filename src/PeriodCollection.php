<?php

namespace Spatie\Period;

use ArrayAccess;
use Countable;
use Iterator;

class PeriodCollection implements
    ArrayAccess,
    Iterator,
    Countable
{
    use IterableImplementation;

    /** @var \League\Period\Period[] */
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
            if (
                $start === null
                || $start > $period->getStart()
            ) {
                $start = $period->getStart();
            }

            if (
                $end === null
                || $end < $period->getEnd()
            ) {
                $end = $period->getEnd();
            }
        }

        if (! $start || ! $end) {
            return null;
        }

        return new Period($start, $end);
    }

    public function gaps(): PeriodCollection
    {
        $boundaries = $this->boundaries();

        if (! $boundaries) {
            return new PeriodCollection();
        }

        return $boundaries->diff(...$this);
    }

    public function isEmpty(): bool
    {
        return count($this->periods) === 0;
    }
}

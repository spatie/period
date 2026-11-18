<?php

namespace Spatie\Period;

use ArrayAccess;
use Closure;
use Countable;
use DateTimeImmutable;
use Iterator;
use Spatie\Period\Exceptions\CannotComparePeriods;

class PeriodCollection implements ArrayAccess, Iterator, Countable
{
    use IterableImplementation;

    /** @var \Spatie\Period\Period[] */
    protected array $periods;

    public static function make(Period ...$periods): static
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

    public function overlapAll(PeriodCollection ...$others): PeriodCollection
    {
        $overlap = clone $this;

        foreach ($others as $other) {
            $overlap = $overlap->overlap($other);
        }

        return $overlap;
    }

    public function boundaries(): ?Period
    {
        $start = null;
        $end = null;

        foreach ($this->periods as $period) {
            if ($start === null || $start > $period->includedStart()) {
                $start = $period->includedStart();
            }

            if ($end === null || $end < $period->includedEnd()) {
                $end = $period->includedEnd();
            }
        }

        if (! $start || ! $end) {
            return null;
        }

        [$firstPeriod] = $this->periods;

        return Period::make(
            $start,
            $end,
            $firstPeriod->precision(),
            Boundaries::EXCLUDE_NONE()
        );
    }

    public function gaps(): static
    {
        $boundaries = $this->boundaries();

        if (! $boundaries) {
            return static::make();
        }

        return $boundaries->subtract(...$this);
    }

    public function intersect(Period $intersection): static
    {
        $intersected = static::make();

        foreach ($this->periods as $period) {
            $overlap = $intersection->overlap($period);

            if ($overlap === null) {
                continue;
            }

            $intersected[] = $overlap;
        }

        return $intersected;
    }

    public function add(Period ...$periods): static
    {
        $collection = clone $this;

        foreach ($periods as $period) {
            $collection[] = $period;
        }

        return $collection;
    }

    public function map(Closure $closure): static
    {
        $collection = clone $this;

        foreach ($collection->periods as $key => $period) {
            $collection->periods[$key] = $closure($period);
        }

        return $collection;
    }

    public function reduce(Closure $closure, $initial = null): mixed
    {
        $carry = $initial;

        foreach ($this->periods as $period) {
            $carry = $closure($carry, $period);
        }

        return $carry;
    }

    public function filter(Closure $closure): static
    {
        $collection = clone $this;

        $collection->periods = array_values(array_filter($collection->periods, $closure));

        return $collection;
    }

    public function isEmpty(): bool
    {
        return count($this->periods) === 0;
    }

    public function subtract(PeriodCollection | Period $others): static
    {
        if ($others instanceof Period) {
            $others = new static($others);
        }

        if ($others->count() === 0) {
            return clone $this;
        }

        $periods = [];

        foreach ($this->periods as $period) {
            array_push($periods, ...$period->subtract(...$others->periods)->periods);
        }

        return new static(...$periods);
    }

    private function overlap(PeriodCollection $others): PeriodCollection
    {
        $overlaps = new PeriodCollection();

        if ($this->isEmpty() || $others->isEmpty()) {
            return $overlaps;
        }

        // Only candidate pairs get compared below, so check every precision up front
        $precision = reset($this->periods)->precision();

        foreach ([...$this->periods, ...$others->periods] as $period) {
            if (! $precision->equals($period->precision())) {
                throw CannotComparePeriods::precisionDoesNotMatch();
            }
        }

        $sorted = $others->periods;
        $starts = array_map(static fn (Period $period) => $period->includedStart(), $sorted);
        $keys = array_keys($sorted);

        // The keys break ties, so equal starts keep their order and periods are never compared
        array_multisort($starts, $keys, $sorted);

        // The latest end up to each position: it only grows, so it can be searched like the starts
        $ends = [];
        $end = null;

        foreach ($sorted as $other) {
            $end = $end === null ? $other->includedEnd() : max($end, $other->includedEnd());
            $ends[] = $end;
        }

        foreach ($this->periods as $period) {
            $first = self::search($ends, $period->includedStart(), true);
            $last = self::search($starts, $period->includedEnd(), false);
            $matches = [];

            for ($i = $first; $i < $last; $i++) {
                $overlap = $period->overlap($sorted[$i]);

                if ($overlap !== null) {
                    $matches[$keys[$i]] = $overlap;
                }
            }

            // Keep the order of the others, as comparing every pair did
            ksort($matches);

            foreach ($matches as $overlap) {
                $overlaps[] = $overlap;
            }
        }

        return $overlaps;
    }

    /** Returns the first index whose date is after the given one, or equal to it when $orEqual is set */
    private static function search(array $dates, DateTimeImmutable $date, bool $orEqual): int
    {
        $low = 0;
        $high = count($dates);

        while ($low < $high) {
            $middle = intdiv($low + $high, 2);

            if ($dates[$middle] > $date || ($orEqual && $dates[$middle] == $date)) {
                $high = $middle;
            } else {
                $low = $middle + 1;
            }
        }

        return $low;
    }

    public function unique(): PeriodCollection
    {
        $uniquePeriods = [];
        foreach ($this->periods as $period) {
            $uniquePeriods[$period->asString()] = $period;
        }

        return new static(...array_values($uniquePeriods));
    }

    public function sort(): PeriodCollection
    {
        $collection = clone $this;

        $starts = array_map(static fn (Period $period) => $period->includedStart(), $collection->periods);
        $keys = array_keys($collection->periods);

        // The keys break ties, so equal starts keep their order and periods are never compared
        array_multisort($starts, $keys, $collection->periods);

        return $collection;
    }

    public function union(): PeriodCollection
    {
        $boundaries = $this->boundaries();

        if (! $boundaries) {
            return static::make();
        }

        return static::make($boundaries)->subtract($boundaries->subtract(...$this));
    }
}

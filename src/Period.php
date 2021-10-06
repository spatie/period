<?php

namespace Spatie\Period;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use IteratorAggregate;
use Spatie\Period\Exceptions\CannotComparePeriods;
use Spatie\Period\Exceptions\InvalidPeriod;
use Spatie\Period\PeriodTraits\PeriodComparisons;
use Spatie\Period\PeriodTraits\PeriodGetters;
use Spatie\Period\PeriodTraits\PeriodOperations;

class Period implements IteratorAggregate
{
    use PeriodGetters;
    use PeriodComparisons;
    use PeriodOperations;

    protected PeriodDuration $duration;

    protected DateTimeImmutable $includedStart;

    protected DateTimeImmutable $includedEnd;

    protected DateInterval $interval;

    public function __construct(
        protected DateTimeImmutable $start,
        protected DateTimeImmutable $end,
        protected Precision $precision,
        protected Boundaries $boundaries
    ) {
        if ($start > $end) {
            throw InvalidPeriod::endBeforeStart($start, $end);
        }

        $this->interval = $this->precision->interval();
        $this->includedStart = $boundaries->startIncluded() ? $start : $start->add($this->interval);
        $this->includedEnd = $boundaries->endIncluded() ? $end : $end->sub($this->interval);
        $this->duration = new PeriodDuration($this);
    }

    public static function make(
        DateTimeInterface | string $start,
        DateTimeInterface | string $end,
        ?Precision $precision = null,
        ?Boundaries $boundaries = null,
        ?string $format = null
    ): static {
        return PeriodFactory::make(
            periodClass: static::class,
            start: $start,
            end: $end,
            precision: $precision,
            boundaries: $boundaries,
            format: $format,
        );
    }

    public static function fromString(string $string): static
    {
        return PeriodFactory::fromString(static::class, $string);
    }

    public function getIterator(): DatePeriod
    {
        return new DatePeriod(
            $this->includedStart(),
            $this->interval,
            // We need to add 1 second (the smallest unit available within this package) to ensure entries are counted correctly
            $this->includedEnd()->add(new DateInterval('PT1S'))
        );
    }

    protected function ensurePrecisionMatches(Period $other): void
    {
        if ($this->precision->equals($other->precision)) {
            return;
        }

        throw CannotComparePeriods::precisionDoesNotMatch();
    }
}

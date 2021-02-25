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

    private string $asString;

    private PeriodDuration $duration;

    public function __construct(
        protected DateTimeImmutable $start,
        protected DateTimeImmutable $end,
        protected DateTimeImmutable $includedStart,
        protected DateTimeImmutable $includedEnd,
        protected DateInterval $interval,
        protected Precision $precision,
        protected Boundaries $boundaries
    ) {
        if ($start > $end) {
            throw InvalidPeriod::endBeforeStart($start, $end);
        }

        $this->duration = new PeriodDuration($this);
        $this->asString = $this->resolveString();
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

    private function resolveString(): string
    {
        $string = '';

        if ($this->isStartIncluded()) {
            $string .= '[';
        } else {
            $string .= '(';
        }

        $string .= $this->start()->format($this->precision->dateFormat());

        $string .= ',';

        $string .= $this->end()->format($this->precision->dateFormat());

        if ($this->isEndIncluded()) {
            $string .= ']';
        } else {
            $string .= ')';
        }

        return $string;
    }
}

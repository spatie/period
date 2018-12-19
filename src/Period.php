<?php

namespace Spatie\Period;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Spatie\Period\Exceptions\InvalidDate;
use Spatie\Period\Exceptions\InvalidPeriod;

class Period
{
    const EXCLUDE_NONE = 0;
    const EXCLUDE_START = 2;
    const EXCLUDE_END = 4;
    const EXCLUDE_ALL = 6;

    /** @var \DateTimeImmutable */
    protected $start;

    /** @var \DateTimeImmutable */
    protected $end;

    /** @var int */
    protected $exclusionMask;

    public function __construct(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        int $exclusionMask = 0
    ) {
        if ($start > $end) {
            throw InvalidPeriod::endBeforeStart($start, $end);
        }

        $this->start = $start;
        $this->end = $end;
        $this->exclusionMask = $exclusionMask;
    }

    /**
     * @param \DateTimeInterface|string $start
     * @param \DateTimeInterface|string $end
     * @param string|null $format
     *
     * @return \Spatie\Period\Period|static
     */
    public static function make(
        $start,
        $end,
        ?string $format = null,
        int $exclusionMask = 0
    ): Period {
        if ($start === null) {
            throw InvalidDate::cannotBeNull('Start date');
        }

        if ($end === null) {
            throw InvalidDate::cannotBeNull('End date');
        }

        return new static(
            self::resolveDate($start, $format),
            self::resolveDate($end, $format),
            $exclusionMask
        );
    }

    public function startIncluded(): bool
    {
        return ! $this->startExcluded();
    }

    public function startExcluded(): bool
    {
        return self::EXCLUDE_START & $this->exclusionMask;
    }

    public function endIncluded(): bool
    {
        return ! $this->endExcluded();
    }

    public function endExcluded(): bool
    {
        return self::EXCLUDE_END & $this->exclusionMask;
    }

    protected static function resolveDate($date, ?string $format): DateTimeImmutable
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($date);
        }

        $format = self::resolveFormat($date, $format);

        if (! is_string($date)) {
            throw InvalidDate::forFormat($date, $format);
        }

        $dateTime = DateTimeImmutable::createFromFormat($format, $date);

        if ($dateTime === false) {
            throw InvalidDate::forFormat($date, $format);
        }

        if (strpos($format, ' ') === false) {
            $dateTime = $dateTime->setTime(0, 0, 0);
        }

        return $dateTime;
    }

    protected static function resolveFormat($date, ?string $format): string
    {
        if ($format !== null) {
            return $format;
        }

        if (
            strpos($format, ' ') === false
            && strpos($date, ' ') !== false
        ) {
            return 'Y-m-d H:i:s';
        }

        return 'Y-m-d';
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function length(): int
    {
        return $this->start->diff($this->end)->days + 1;
    }

    public function overlapsWith(Period $period): bool
    {
        if ($this->start > $period->end) {
            return false;
        }

        if ($period->start > $this->end) {
            return false;
        }

        return true;
    }

    public function touchesWith(Period $period): bool
    {
        if ($this->end->diff($period->start)->days <= 1) {
            return true;
        }

        if ($this->start->diff($period->end)->days <= 1) {
            return true;
        }

        return false;
    }

    public function startsAfterOrAt(DateTimeInterface $date): bool
    {
        return $this->start >= $date;
    }

    public function endsAfterOrAt(DateTimeInterface $date): bool
    {
        return $this->end >= $date;
    }

    public function startsBeforeOrAt(DateTimeInterface $date): bool
    {
        return $this->start <= $date;
    }

    public function endsBeforeOrAt(DateTimeInterface $date): bool
    {
        return $this->end <= $date;
    }

    public function startsAfter(DateTimeInterface $date): bool
    {
        return $this->start > $date;
    }

    public function endsAfter(DateTimeInterface $date): bool
    {
        return $this->end > $date;
    }

    public function startsBefore(DateTimeInterface $date): bool
    {
        return $this->start < $date;
    }

    public function endsBefore(DateTimeInterface $date): bool
    {
        return $this->end < $date;
    }

    public function contains(DateTimeInterface $date): bool
    {
        if ($date < $this->start) {
            return false;
        }

        if ($date > $this->end) {
            return false;
        }

        return true;
    }

    public function equals(Period $period): bool
    {
        if ($period->start->getTimestamp() !== $this->start->getTimestamp()) {
            return false;
        }

        if ($period->end->getTimestamp() !== $this->end->getTimestamp()) {
            return false;
        }

        return true;
    }

    /**
     * @param \Spatie\Period\Period $period
     *
     * @return \Spatie\Period\Period|static|null
     * @throws \Exception
     */
    public function gap(Period $period): ?Period
    {
        if ($this->overlapsWith($period)) {
            return null;
        }

        if ($this->touchesWith($period)) {
            return null;
        }

        if ($this->start >= $period->end) {
            return static::make(
                $period->end->add(new DateInterval('P1D')),
                $this->start->sub(new DateInterval('P1D'))
            );
        }

        return static::make(
            $this->end->add(new DateInterval('P1D')),
            $period->start->sub(new DateInterval('P1D'))
        );
    }

    /**
     * @param \Spatie\Period\Period $period
     *
     * @return \Spatie\Period\Period|static|null
     */
    public function overlapSingle(Period $period): ?Period
    {
        $start = $this->start > $period->start
            ? $this->start
            : $period->start;

        $end = $this->end < $period->end
            ? $this->end
            : $period->end;

        if ($start > $end) {
            return null;
        }

        return static::make($start, $end);
    }

    /**
     * @param \Spatie\Period\Period ...$periods
     *
     * @return \Spatie\Period\PeriodCollection|static[]
     */
    public function overlap(Period ...$periods): PeriodCollection
    {
        $overlapCollection = new PeriodCollection();

        foreach ($periods as $period) {
            $overlap = $this->overlapSingle($period);

            if ($overlap === null) {
                continue;
            }

            $overlapCollection[] = $overlap;
        }

        return $overlapCollection;
    }

    /**
     * @param \Spatie\Period\Period ...$periods
     *
     * @return \Spatie\Period\Period|static
     */
    public function overlapAll(Period ...$periods): Period
    {
        $overlap = clone $this;

        if (! count($periods)) {
            return $overlap;
        }

        foreach ($periods as $period) {
            $overlap = $overlap->overlapSingle($period);
        }

        return $overlap;
    }

    public function diffSingle(Period $period): PeriodCollection
    {
        $periodCollection = new PeriodCollection();

        if (! $this->overlapsWith($period)) {
            $periodCollection[] = clone $this;
            $periodCollection[] = clone $period;

            return $periodCollection;
        }

        $overlap = $this->overlapSingle($period);

        $start = $this->start < $period->start
            ? $this->start
            : $period->start;

        $end = $this->end > $period->end
            ? $this->end
            : $period->end;

        if ($overlap->start > $start) {
            $periodCollection[] = static::make(
                $start,
                $overlap->start->sub(new DateInterval('P1D'))
            );
        }

        if ($overlap->end < $end) {
            $periodCollection[] = static::make(
                $overlap->end->add(new DateInterval('P1D')),
                $end
            );
        }

        return $periodCollection;
    }

    /**
     * @param \Spatie\Period\Period ...$periods
     *
     * @return \Spatie\Period\PeriodCollection|static[]
     */
    public function diff(Period ...$periods): PeriodCollection
    {
        if (count($periods) === 1 && ! $this->overlapsWith($periods[0])) {
            $collection = new PeriodCollection();

            $collection[] = $this->gap($periods[0]);

            return $collection;
        }

        $diffs = [];

        foreach ($periods as $period) {
            $diffs[] = $this->diffSingle($period);
        }

        $collection = (new PeriodCollection($this))->overlap(...$diffs);

        return $collection;
    }
}

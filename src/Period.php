<?php

namespace Spatie\Period;

use DateTime;
use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use IteratorAggregate;
use Spatie\Period\Exceptions\InvalidDate;
use Spatie\Period\Exceptions\InvalidPeriod;
use Spatie\Period\Exceptions\CannotComparePeriods;

class Period implements IteratorAggregate
{
    /** @var \DateTimeImmutable */
    protected $start;

    /** @var \DateTimeImmutable */
    protected $end;

    /** @var \DateInterval */
    protected $interval;

    /** @var \DateTimeImmutable */
    private $includedStart;

    /** @var \DateTimeImmutable */
    private $includedEnd;

    /** @var int */
    private $boundaryExclusionMask;

    /** @var int */
    private $precisionMask;

    /**
     * Period constructor.
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @param int|null $precisionMask
     * @param int|null $boundaryExclusionMask
     * @throws \Exception
     */
    public function __construct(
        DateTimeImmutable $start,
        ?DateTimeImmutable $end,
        ?int $precisionMask = null,
        ?int $boundaryExclusionMask = null
    ) {
        if ($start > $end && null != $end) {
            throw InvalidPeriod::endBeforeStart($start, $end);
        }

        $this->boundaryExclusionMask = $boundaryExclusionMask ?? Boundaries::EXCLUDE_NONE;
        $this->precisionMask = $precisionMask ?? Precision::DAY;

        $this->start = $this->roundDate($start, $this->precisionMask);
        if (null != $end) {
            $this->end = $this->roundDate($end, $this->precisionMask);
        } else {
            $this->end = null;
        }

        $this->interval = $this->createDateInterval($this->precisionMask);

        $this->includedStart = $this->startIncluded()
            ? $this->start
            : $this->start->add($this->interval);

        $this->includedEnd = $this->endIncluded()
            ? $this->end
            : $this->end->sub($this->interval);
    }

    /**
     * @param string|DateTimeInterface $start
     * @param string|DateTimeInterface $end
     * @param int|null $precisionMask
     * @param int|null $boundaryExclusionMask
     * @param string|null $format
     *
     * @return static
     */
    public static function make(
        $start,
        $end,
        ?int $precisionMask = null,
        ?int $boundaryExclusionMask = null,
        ?string $format = null
    ): Period {
        if ($start === null) {
            throw InvalidDate::cannotBeNull('Start date');
        }

//        if ($end === null) {
//            throw InvalidDate::cannotBeNull('End date');
//        }

        return new static(
            static::resolveDate($start, $format),
            $end ? static::resolveDate($end, $format) : null,
            $precisionMask,
            $boundaryExclusionMask
        );
    }

    /**
     * @return bool
     */
    public function startIncluded(): bool
    {
        return ! $this->startExcluded();
    }

    /**
     * @return bool
     */
    public function startExcluded(): bool
    {
        return Boundaries::EXCLUDE_START & $this->boundaryExclusionMask;
    }

    /**
     * @return bool
     */
    public function endIncluded(): bool
    {
        return ! $this->endExcluded();
    }

    /**
     * @return bool
     */
    public function endExcluded(): bool
    {
        return Boundaries::EXCLUDE_END & $this->boundaryExclusionMask;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getIncludedStart(): DateTimeImmutable
    {
        return $this->includedStart;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getIncludedEnd(): DateTimeImmutable
    {
        return $this->includedEnd;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        $length = $this->getIncludedStart()->diff($this->getIncludedEnd())->days + 1;

        return $length;
    }

    /**
     * @param Period $period
     * @return bool
     * @throws CannotComparePeriods
     */
    public function overlapsWith(Period $period): bool
    {
        $this->ensurePrecisionMatches($period);

        // if all Period have end
        if (null != $this->end && null != $period->end) {
            if ($this->getIncludedStart() > $period->getIncludedEnd()) {
                return false;
            }

            if ($period->getIncludedStart() > $this->getIncludedEnd()) {
                return false;
            }

            return true;

        // if one of the Periods does not have end
        } else {

            // if this Period end have null and another Period end don`t
            if (null == $this->end && null != $period->end) {
                if ($this->getIncludedStart() > $period->getIncludedEnd()) {
                    return false;
                }

                return true;
            }

            // if this Period end don`t null and another Period end have null
            if (null != $this->end && null == $period->end) {
                if ($period->getIncludedStart() > $this->getIncludedEnd()) {
                    return false;
                }

                return true;
            }

            // if above Period end have null
            if (null == $this->end && null == $period->end) {
                return true;
            }
        }

    }

    /**
     * @param Period $period
     * @return bool
     * @throws CannotComparePeriods
     */
    public function touchesWith(Period $period): bool
    {
        $this->ensurePrecisionMatches($period);

        if ($this->getIncludedEnd()->diff($period->getIncludedStart())->days <= 1) {
            return true;
        }

        if ($this->getIncludedStart()->diff($period->getIncludedEnd())->days <= 1) {
            return true;
        }

        return false;
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function startsBefore(DateTimeInterface $date): bool
    {
        return $this->getIncludedStart() < $date;
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function startsBeforeOrAt(DateTimeInterface $date): bool
    {
        return $this->getIncludedStart() <= $date;
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function startsAfter(DateTimeInterface $date): bool
    {
        return $this->getIncludedStart() > $date;
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function startsAfterOrAt(DateTimeInterface $date): bool
    {
        return $this->getIncludedStart() >= $date;
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function startsAt(DateTimeInterface $date): bool
    {
        return $this->getIncludedStart()->getTimestamp() === $this->roundDate(
            $date,
            $this->precisionMask
        )->getTimestamp();
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function endsBefore(DateTimeInterface $date): bool
    {
        return $this->getIncludedEnd() < $this->roundDate(
                $date,
                $this->precisionMask
            );
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function endsBeforeOrAt(DateTimeInterface $date): bool
    {
        return $this->getIncludedEnd() <= $this->roundDate(
                $date,
                $this->precisionMask
            );
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function endsAfter(DateTimeInterface $date): bool
    {
        return $this->getIncludedEnd() > $this->roundDate(
                $date,
                $this->precisionMask
            );
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function endsAfterOrAt(DateTimeInterface $date): bool
    {
        return $this->getIncludedEnd() >= $this->roundDate(
                $date,
                $this->precisionMask
            );
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function endsAt(DateTimeInterface $date): bool
    {
        return $this->getIncludedEnd()->getTimestamp() === $this->roundDate(
                $date,
                $this->precisionMask
            )->getTimestamp();
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function contains(DateTimeInterface $date): bool
    {
        if ($this->roundDate($date, $this->precisionMask) < $this->getIncludedStart()) {
            return false;
        }

        if ($this->roundDate($date, $this->precisionMask) > $this->getIncludedEnd()) {
            return false;
        }

        return true;
    }

    /**
     * @param Period $period
     * @return bool
     * @throws CannotComparePeriods
     */
    public function equals(Period $period): bool
    {
        $this->ensurePrecisionMatches($period);

        if ($period->getIncludedStart()->getTimestamp() !== $this->getIncludedStart()->getTimestamp()) {
            return false;
        }

        if ($period->getIncludedEnd()->getTimestamp() !== $this->getIncludedEnd()->getTimestamp()) {
            return false;
        }

        return true;
    }

    /**
     * @param \Spatie\Period\Period $period
     *
     * @return static|null
     * @throws \Exception
     */
    public function gap(Period $period): ?Period
    {
        $this->ensurePrecisionMatches($period);

        if ($this->overlapsWith($period)) {
            return null;
        }

        if ($this->touchesWith($period)) {
            return null;
        }

        if ($this->getIncludedStart() >= $period->getIncludedEnd()) {
            return static::make(
                $period->getIncludedEnd()->add($this->interval),
                $this->getIncludedStart()->sub($this->interval),
                $this->getPrecisionMask()
            );
        }

        return static::make(
            $this->getIncludedEnd()->add($this->interval),
            $period->getIncludedStart()->sub($this->interval),
            $this->getPrecisionMask()
        );
    }

    /**
     * @param Period $period
     * @return Period|null
     * @throws CannotComparePeriods
     */
    public function overlapSingle(Period $period): ?Period
    {
        $this->ensurePrecisionMatches($period);

        $start = $this->getIncludedStart() > $period->getIncludedStart()
            ? $this->getIncludedStart()
            : $period->getIncludedStart();

        $end = $this->getIncludedEnd() < $period->getIncludedEnd()
            ? $this->getIncludedEnd()
            : $period->getIncludedEnd();

        if ($start > $end) {
            return null;
        }

        return static::make($start, $end, $this->getPrecisionMask());
    }

    /**
     * @param Period ...$periods
     * @return PeriodCollection
     * @throws CannotComparePeriods
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
     * @param Period ...$periods
     * @return Period
     * @throws CannotComparePeriods
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

    /**
     * @param Period $period
     * @return PeriodCollection
     * @throws CannotComparePeriods
     */
    public function diffSingle(Period $period): PeriodCollection
    {
        $this->ensurePrecisionMatches($period);

        $periodCollection = new PeriodCollection();

        if (! $this->overlapsWith($period)) {
            $periodCollection[] = clone $this;
            $periodCollection[] = clone $period;

            return $periodCollection;
        }

        $overlap = $this->overlapSingle($period);

        $start = $this->getIncludedStart() < $period->getIncludedStart()
            ? $this->getIncludedStart()
            : $period->getIncludedStart();

        $end = $this->getIncludedEnd() > $period->getIncludedEnd()
            ? $this->getIncludedEnd()
            : $period->getIncludedEnd();

        if ($overlap->getIncludedStart() > $start) {
            $periodCollection[] = static::make(
                $start,
                $overlap->getIncludedStart()->sub($this->interval),
                $this->getPrecisionMask()
            );
        }

        if ($overlap->getIncludedEnd() < $end) {
            $periodCollection[] = static::make(
                $overlap->getIncludedEnd()->add($this->interval),
                $end,
                $this->getPrecisionMask()
            );
        }

        return $periodCollection;
    }

    /**
     * @param Period ...$periods
     * @return PeriodCollection
     * @throws CannotComparePeriods
     */
    public function diff(Period ...$periods): PeriodCollection
    {
        if (count($periods) === 1 && ! $this->overlapsWith($periods[0])) {
            $collection = new PeriodCollection();

            $gap = $this->gap($periods[0]);

            if ($gap !== null) {
                $collection[] = $gap;
            }

            return $collection;
        }

        $diffs = [];

        foreach ($periods as $period) {
            $diffs[] = $this->diffSingle($period);
        }

        $collection = (new PeriodCollection($this))->overlap(...$diffs);

        return $collection;
    }

    /**
     * @return int
     */
    public function getPrecisionMask(): int
    {
        return $this->precisionMask;
    }

    /**
     * @return DatePeriod|\Traversable
     */
    public function getIterator()
    {
        return new DatePeriod(
            $this->getIncludedStart(),
            $this->interval,
            $this->getIncludedEnd()->add($this->interval)
        );
    }

    /**
     * @param $date
     * @param string|null $format
     * @return DateTimeImmutable
     */
    protected static function resolveDate($date, ?string $format): DateTimeImmutable
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($date);
        }

        $format = static::resolveFormat($date, $format);

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

    /**
     * @param $date
     * @param string|null $format
     * @return string
     */
    protected static function resolveFormat($date, ?string $format): string
    {
        if ($format !== null) {
            return $format;
        }

        if (strpos($format, ' ') === false && strpos($date, ' ') !== false) {
            return 'Y-m-d H:i:s';
        }

        return 'Y-m-d';
    }

    /**
     * @param DateTimeInterface $date
     * @param int $precision
     * @return DateTimeImmutable
     */
    protected function roundDate(DateTimeInterface $date, int $precision): DateTimeImmutable
    {
        [$year, $month, $day, $hour, $minute, $second] = explode(' ', $date->format('Y m d H i s'));

        $month = (Precision::MONTH & $precision) === Precision::MONTH ? $month : '01';
        $day = (Precision::DAY & $precision) === Precision::DAY ? $day : '01';
        $hour = (Precision::HOUR & $precision) === Precision::HOUR ? $hour : '00';
        $minute = (Precision::MINUTE & $precision) === Precision::MINUTE ? $minute : '00';
        $second = (Precision::SECOND & $precision) === Precision::SECOND ? $second : '00';

        return DateTimeImmutable::createFromFormat(
            'Y m d H i s',
            implode(' ', [$year, $month, $day, $hour, $minute, $second])
        );
    }

    /**
     * @param int $precision
     * @return DateInterval
     * @throws \Exception
     */
    protected function createDateInterval(int $precision): DateInterval
    {
        $interval = [
            Precision::SECOND => 'PT1S',
            Precision::MINUTE => 'PT1M',
            Precision::HOUR => 'PT1H',
            Precision::DAY => 'P1D',
            Precision::MONTH => 'P1M',
            Precision::YEAR => 'P1Y',
        ][$precision];

        return new DateInterval($interval);
    }

    /**
     * @param Period $period
     * @throws CannotComparePeriods
     */
    protected function ensurePrecisionMatches(Period $period): void
    {
        if ($this->precisionMask === $period->precisionMask) {
            return;
        }

        throw CannotComparePeriods::precisionDoesNotMatch();
    }
}

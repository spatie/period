<?php

declare(strict_types=1);

namespace Spatie\Period;

use DateInterval;
use DateTimeImmutable;

final class Duration
{
    /** @var int */
    private $seconds;

    /** @var int */
    private $precision;

    private function __construct()
    {
    }

    public static function make($value): self
    {
        if ($value instanceof Period) {
            return self::fromPeriod($value);
        }

        if ($value instanceof PeriodCollection) {
            return self::fromPeriodCollection($value);
        }

        if ($value instanceof DateInterval) {
            return self::fromDateInterval($value);
        }

        $stringValue = (string) $value;

        if (0 === strpos($stringValue, 'P')) {
            return self::fromDateInterval(new DateInterval($stringValue));
        }

        return self::fromDateInterval(DateInterval::createFromDateString($stringValue));
    }

    public static function fromPeriod(Period $period): self
    {
        $start = $period->getIncludedStart();
        $end = $period->getIncludedEnd();
        $precision = $period->getPrecisionMask();

        if ($precision === Precision::SECOND) {
            $end = $end->add(new DateInterval('PT1S'));
        } elseif ($precision === Precision::MINUTE) {
            $end = $end->add(new DateInterval('PT1M'));
        } elseif ($precision === Precision::HOUR) {
            $end = $end->add(new DateInterval('PT1H'));
        } elseif ($precision === Precision::DAY) {
            $end = $end->add(new DateInterval('PT24H'));
        } else {
            throw new \InvalidArgumentException('A duration can only be determined up to a precision of Precision::DAY');
        }

        $duration = new self();
        $duration->seconds = $end->getTimestamp() - $start->getTimestamp();
        $duration->precision = $precision;

        return $duration;
    }

    public static function fromPeriodCollection(PeriodCollection $collection): self
    {
        $duration = self::none();

        foreach ($collection as $period) {
            $duration = $duration->withAdded($period->duration());
        }

        return $duration;
    }

    public static function fromDateInterval(DateInterval $interval): self
    {
        $now = new DateTimeImmutable();
        $then = $now->add($interval);

        $duration = new self();
        $duration->seconds = $then->getTimestamp() - $now->getTimestamp();
        $duration->precision = self::determinePrecisionFromSeconds($duration->seconds);

        return $duration;
    }

    public static function none(): self
    {
        $new = new self();
        $new->seconds = 0;
        $new->precision = Precision::SECOND;

        return $new;
    }

    public function length(int $precision): int
    {
        switch ($precision) {
            case Precision::SECOND:
                return $this->seconds;
            case Precision::MINUTE:
                return intdiv($this->seconds, 60);
            case Precision::HOUR:
                return intdiv($this->seconds, 3600);
            case Precision::DAY:
                return intdiv($this->seconds, 86400);
            default:
                throw new \InvalidArgumentException('Unsupported precision for durations');
        }
    }

    public function precision(): int
    {
        return $this->precision;
    }

    public function withAdded(self $other): self
    {
        $new = clone $this;
        $new->seconds = $this->seconds + $other->length(Precision::SECOND);
        $new->precision = $this->largestCommonPrecision($other);

        return $new;
    }

    public function compareTo(self $other): int
    {
        $precision = $this->largestCommonPrecision($other);

        return $this->length($precision) <=> $other->length($precision);
    }

    public function isLargerThan(self $other): bool
    {
        return 1 === $this->compareTo($other);
    }

    public function equals(self $other): bool
    {
        return 0 === $this->compareTo($other);
    }

    public function isSmallerThan(self $other): bool
    {
        return -1 === $this->compareTo($other);
    }

    private function largestCommonPrecision(self $other): int
    {
        return $this->precision >= $other->precision() ? $this->precision : $other->precision();
    }

    private static function determinePrecisionFromSeconds(int $seconds): int
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($remainingSeconds !== 0) {
            return Precision::SECOND;
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes !== 0) {
            return Precision::MINUTE;
        }

        // $days = intdiv($hours, 24);
        $remainingHours = $hours % 24;

        if ($remainingHours !== 0) {
            return Precision::HOUR;
        }

        // Months and years are variable:
        // A month could have 28 to 31 days
        // A year can have 365 or 366 days (leap years)
        return Precision::DAY;
    }
}

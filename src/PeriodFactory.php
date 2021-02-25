<?php

namespace Spatie\Period;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Spatie\Period\Exceptions\InvalidDate;

class PeriodFactory
{
    public static function make(
        string $periodClass,
        string | DateTimeInterface $start,
        string | DateTimeInterface $end,
        ?Precision $precision = null,
        ?Boundaries $boundaries = null,
        ?string $format = null
    ): Period {
        $boundaries ??= Boundaries::EXCLUDE_NONE();
        $precision ??= Precision::DAY();
        $start = $precision->roundDate(self::resolveDate($start, $format));
        $end = $precision->roundDate(self::resolveDate($end, $format));

        /** @var \Spatie\Period\Period $period */
        $period = new $periodClass(
            start: $start,
            end: $end,
            precision: $precision,
            boundaries: $boundaries,
        );

        return $period;
    }

    protected static function resolveDate(
        DateTimeInterface | string $date,
        ?string $format
    ): DateTimeImmutable {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($date);
        }

        if (! is_string($date)) {
            throw InvalidDate::forFormat($date, $format);
        }

        $format = static::resolveFormat($date, $format);

        $dateTime = DateTimeImmutable::createFromFormat($format, $date);

        if ($dateTime === false) {
            throw InvalidDate::forFormat($date, $format);
        }

        if (! str_contains($format, ' ')) {
            $dateTime = $dateTime->setTime(0, 0, 0);
        }

        return $dateTime;
    }

    protected static function resolveFormat(
        string $date,
        ?string $format
    ): string {
        if ($format !== null) {
            return $format;
        }

        if (! str_contains($format, ' ') && str_contains($date, ' ')) {
            return 'Y-m-d H:i:s';
        }

        return 'Y-m-d';
    }
}

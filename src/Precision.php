<?php

namespace Spatie\Period;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class Precision
{
    private const YEAR = 0b100000;
    private const MONTH = 0b110000;
    private const DAY = 0b111000;
    private const HOUR = 0b111100;
    private const MINUTE = 0b111110;
    private const SECOND = 0b111111;

    /**
     * @return self[]
     */
    public static function all(): array
    {
        return [
            self::YEAR(),
            self::MONTH(),
            self::DAY(),
            self::HOUR(),
            self::MINUTE(),
            self::SECOND(),
        ];
    }

    private function __construct(
        private int $mask
    ) {
    }

    public static function YEAR(): self
    {
        return new self(self::YEAR);
    }

    public static function MONTH(): self
    {
        return new self(self::MONTH);
    }

    public static function DAY(): self
    {
        return new self(self::DAY);
    }

    public static function HOUR(): self
    {
        return new self(self::HOUR);
    }

    public static function MINUTE(): self
    {
        return new self(self::MINUTE);
    }

    public static function SECOND(): self
    {
        return new self(self::SECOND);
    }

    public function interval(): DateInterval
    {
        $interval = match ($this->mask) {
            self::SECOND => 'PT1S',
            self::MINUTE => 'PT1M',
            self::HOUR => 'PT1H',
            self::DAY => 'P1D',
            self::MONTH => 'P1M',
            self::YEAR => 'P1Y',
        };

        return new DateInterval($interval);
    }

    public function intervalName(): string
    {
        return match ($this->mask) {
            self::YEAR => 'y',
            self::MONTH => 'm',
            self::DAY => 'd',
            self::HOUR => 'h',
            self::MINUTE => 'i',
            self::SECOND => 's',
        };
    }

    public function roundDate(DateTimeInterface $date): DateTimeImmutable
    {
        [$year, $month, $day, $hour, $minute, $second] = explode(' ', $date->format('Y m d H i s'));

        $month = (self::MONTH & $this->mask) === self::MONTH ? $month : '01';
        $day = (self::DAY & $this->mask) === self::DAY ? $day : '01';
        $hour = (self::HOUR & $this->mask) === self::HOUR ? $hour : '00';
        $minute = (self::MINUTE & $this->mask) === self::MINUTE ? $minute : '00';
        $second = (self::SECOND & $this->mask) === self::SECOND ? $second : '00';

        return DateTimeImmutable::createFromFormat(
            'Y m d H i s',
            implode(' ', [$year, $month, $day, $hour, $minute, $second]),
            $date->getTimezone()
        );
    }

    public function equals(Precision ...$others): bool
    {
        foreach ($others as $other) {
            if ($this->mask !== $other->mask) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function dateFormat(): string
    {
        return match ($this->mask) {
            Precision::SECOND => 'Y-m-d H:i:s',
            Precision::MINUTE => 'Y-m-d H:i',
            Precision::HOUR => 'Y-m-d H',
            Precision::DAY => 'Y-m-d',
            Precision::MONTH => 'Y-m',
            Precision::YEAR => 'Y',
        };
    }
}

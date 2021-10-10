<?php

namespace Spatie\Period\PeriodTraits;

use DateTimeImmutable;
use Spatie\Period\Boundaries;
use Spatie\Period\Exceptions\CannotCeilLowerPrecision;
use Spatie\Period\PeriodDuration;
use Spatie\Period\Precision;

/** @mixin \Spatie\Period\Period */
trait PeriodGetters
{
    protected string $asString;

    public function isStartIncluded(): bool
    {
        return $this->boundaries->startIncluded();
    }

    public function isStartExcluded(): bool
    {
        return $this->boundaries->startExcluded();
    }

    public function isEndIncluded(): bool
    {
        return $this->boundaries->endIncluded();
    }

    public function isEndExcluded(): bool
    {
        return $this->boundaries->endExcluded();
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function includedStart(): DateTimeImmutable
    {
        return $this->includedStart;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    public function includedEnd(): DateTimeImmutable
    {
        return $this->includedEnd;
    }

    public function ceilingEnd(?Precision $precision = null): DateTimeImmutable
    {
        $precision ??= $this->precision;

        if ($precision->higherThan($this->precision)) {
            throw CannotCeilLowerPrecision::precisionIsLower($this->precision, $precision);
        }

        return $this->precision->ceilDate($this->includedEnd, $precision);
    }

    public function length(): int
    {
        // Length of month and year are not fixed, so we can't predict the length without iterate
        // TODO: maybe we can use cal_days_in_month ?
        if ($this->precision->equals(Precision::MONTH(), Precision::YEAR())) {
            return iterator_count($this);
        }

        if ($this->precision->equals(Precision::HOUR(), Precision::MINUTE(), Precision::SECOND())) {
            $length = abs($this->includedEnd()->getTimestamp() - $this->includedStart()->getTimestamp());

            if ($this->precision->equals(Precision::SECOND())) {
                return $length + 1;
            }

            $length = floor($length / 60);

            if ($this->precision->equals(Precision::MINUTE())) {
                return $length + 1;
            }

            return floor($length / 60) + 1;
        }

        return $this->includedStart()->diff($this->includedEnd())->days + 1;
    }

    public function duration(): PeriodDuration
    {
        return $this->duration;
    }

    public function precision(): Precision
    {
        return $this->precision;
    }

    public function boundaries(): Boundaries
    {
        return $this->boundaries;
    }

    public function asString(): string
    {
        if (! isset($this->asString)) {
            $this->asString = $this->resolveString();
        }

        return $this->asString;
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

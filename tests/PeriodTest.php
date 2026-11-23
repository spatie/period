<?php

use Carbon\Carbon;
use Spatie\Period\Boundaries;
use Spatie\Period\Exceptions\CannotCeilLowerPrecision;
use Spatie\Period\Exceptions\InvalidDate;
use Spatie\Period\Exceptions\InvalidPeriod;
use Spatie\Period\Period;
use Spatie\Period\Precision;

it('can determine the period length', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    expect($period->length())->toEqual(15);
});

it('returns a length of 0 for an empty period, consistent with its iterator', function (Period $period) {
    expect($period->includedStart())->toBeGreaterThan($period->includedEnd());
    expect($period->length())->toEqual(0);
    expect($period->length())->toEqual(iterator_count($period));
})->with('empty_period_lengths');

it('preserves the length of a nonempty fractional period', function (string $precisionName) {
    $precision = Precision::$precisionName();
    $start = new DateTimeImmutable('2024-01-01 00:00:00.750000 UTC');
    $end = $start->add($precision->interval())->modify('-500000 microseconds');
    $period = new Period($start, $end, $precision, Boundaries::EXCLUDE_START());

    expect(iterator_count($period))->toBe(1);
    expect($period->length())->toBe(1);
})->with(['YEAR', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 'SECOND']);

it('preserves unrounded dates passed to the constructor', function (string $precisionName) {
    $precision = Precision::$precisionName();
    $start = new DateTimeImmutable('2024-01-15 12:00:00 UTC');
    $includedStart = $start->add($precision->interval());
    $end = $includedStart->add($precision->interval())->modify('+1 hour');

    $period = new Period($start, $end, $precision, Boundaries::EXCLUDE_ALL());

    expect($period->includedStart())->toEqual($includedStart);
    expect($period->includedEnd())->toEqual($includedStart->modify('+1 hour'));
})->with(['DAY', 'MONTH', 'YEAR']);

it('does not turn a fractional day across midnight into two days', function () {
    $period = new Period(
        new DateTimeImmutable('2024-01-01 23:00:00 UTC'),
        new DateTimeImmutable('2024-01-02 01:00:00 UTC'),
        Precision::DAY(),
        Boundaries::EXCLUDE_NONE(),
    );

    expect($period->length())->toBe(1);
});

it('uses the overridden iterator for month and year lengths', function (string $precisionName) {
    $period = new class (
        new DateTimeImmutable('2024-01-01 UTC'),
        new DateTimeImmutable('2024-01-01 UTC'),
        Precision::$precisionName(),
        Boundaries::EXCLUDE_ALL(),
    ) extends Period {
        public function getIterator(): DatePeriod
        {
            return new DatePeriod($this->start(), $this->precision()->interval(), 1);
        }
    };

    expect(iterator_count($period))->toBe(2);
    expect($period->length())->toBe(2);
})->with(['MONTH', 'YEAR']);

it('can renew a period', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    $renewal = $period->renew();

    expect($renewal->touchesWith($period))->toBeTrue();
    expect($period->length())->toEqual($renewal->length());
});

it('has a duration', function () {
    $a = Period::make('2018-01-01', '2018-01-15');
    $b = Period::make('2018-02-01', '2018-02-15');

    expect($a->duration()->equals($b->duration()))->toBeTrue();
});

it('accepts carbon instances', function () {
    $a = Period::make(Carbon::make('2018-01-01'), Carbon::make('2018-01-02'));

    expect($a->equals(Period::make('2018-01-01', '2018-01-02')))->toBeTrue();
});

it('will preserve the time', function () {
    $period = Period::make('2018-01-01 01:02:03', '2018-01-02 04:05:06');

    expect($period->equals(Period::make(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 01:02:03'), DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 04:05:06'))))->toBeTrue();
});

it('will use the start of day when passing strings to a period', function () {
    $period = Period::make('2018-01-01', '2018-01-02');

    expect($period->equals(Period::make(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00'), DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00'))))->toBeTrue();
});

it('is iterable', function (int $expectedCount, Period $period) {
    expect(iterator_count($period))->toBe($expectedCount);
})->with('expected_period_lengths');

it('its iterator returns immutable dates', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    $current = $period->getIterator()->start;

    expect($current)->toBeInstanceOf(DateTimeImmutable::class);
});

it('keeps timezone when boundaries are timezoned', function () {
    $timeZone = new DateTimeZone('Europe/London');
    $start = new DateTimeImmutable('2000-01-01', $timeZone);
    $end = new DateTimeImmutable('2000-02-01', $timeZone);
    $period = Period::make($start, $end);
    expect($timeZone)->toEqual($period->start()->getTimezone());
    expect($timeZone)->toEqual($period->end()->getTimezone());
});

it('gets the correct ceiling of a precision', function (Period $period, ?Precision $precision, Carbon $expected) {
    expect($period->ceilingEnd($precision))->toEqual($expected->startOfSecond());
})->with('ceiling_dates');

it('throws if trying to get a ceiling of a lower precision', function () {
    $seconds = Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::HOUR());

    $seconds->ceilingEnd(Precision::SECOND());
})->throws(CannotCeilLowerPrecision::class, "Cannot get the latest hour of a second.");

it('test from string', function () {
    expect(Period::fromString('(2021-01-01 00:00:00 , 2021-01-22 12:12:12]')->asString())->toEqual('(2021-01-01 00:00:00,2021-01-22 12:12:12]');
    expect(Period::fromString('(2021-01-01 00:00 , 2021-01-22 12:12]')->asString())->toEqual('(2021-01-01 00:00,2021-01-22 12:12]');
    expect(Period::fromString('(2021-01-01 00 , 2021-01-22 12]')->asString())->toEqual('(2021-01-01 00,2021-01-22 12]');
    expect(Period::fromString('(2021-01-01 , 2021-01-22]')->asString())->toEqual('(2021-01-01,2021-01-22]');
    expect(Period::fromString('(2021-01 , 2021-01]')->asString())->toEqual('(2021-01,2021-01]');
    expect(Period::fromString('(2021 , 2021]')->asString())->toEqual('(2021,2021]');
    expect(Period::fromString('( 2021 , 2021 )')->asString())->toEqual('(2021,2021)');
    expect(Period::fromString('[ 2021 , 2021 ]')->asString())->toEqual('[2021,2021]');
});

it('cannot end before it starts', function () {
    Period::make('2018-01-10', '2018-01-01');
})->throws(InvalidPeriod::class, 'The end time `2018-01-01 00:00:00` is before the start time `2018-01-10 00:00:00`.');

it('cannot be made from a date that does not match the format', function () {
    Period::make('2018-01-01', '01/31/2018');
})->throws(InvalidDate::class, 'Could not construct a date from `01/31/2018` with format `Y-m-d`');

it('names both precisions when the ceiling precision is higher', function (Precision $periodPrecision, Precision $ceilingPrecision, string $message) {
    expect(fn () => Period::make('2018-01-01', '2018-01-15', $periodPrecision)->ceilingEnd($ceilingPrecision))
        ->toThrow(CannotCeilLowerPrecision::class, $message);
})->with([
    [Precision::YEAR(), Precision::MONTH(), 'Cannot get the latest year of a month.'],
    [Precision::MONTH(), Precision::DAY(), 'Cannot get the latest month of a day.'],
    [Precision::DAY(), Precision::MINUTE(), 'Cannot get the latest day of a minute.'],
    [Precision::MINUTE(), Precision::SECOND(), 'Cannot get the latest minute of a second.'],
]);

it('is not equal to a period with another start or end', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    expect($period->equals(Period::make('2018-01-02', '2018-01-15')))->toBeFalse();
    expect($period->equals(Period::make('2018-01-01', '2018-01-14')))->toBeFalse();
});

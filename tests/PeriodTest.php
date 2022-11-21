<?php

use Carbon\Carbon;
use Spatie\Period\Exceptions\CannotCeilLowerPrecision;
use Spatie\Period\Period;
use Spatie\Period\Precision;

it('can determine the period length', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    expect($period->length())->toEqual(15);
});

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

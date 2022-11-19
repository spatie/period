<?php

use Carbon\Carbon;
use Spatie\Period\Exceptions\CannotCeilLowerPrecision;
use Spatie\Period\Period;
use Spatie\Period\Precision;

it('can determine the period length', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    $this->assertEquals(15, $period->length());
});

it('can renew a period', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    $renewal = $period->renew();

    $this->assertTrue($renewal->touchesWith($period));
    $this->assertEquals($renewal->length(), $period->length());
});

it('has a duration', function () {
    $a = Period::make('2018-01-01', '2018-01-15');
    $b = Period::make('2018-02-01', '2018-02-15');

    $this->assertTrue($a->duration()->equals($b->duration()));
});

it('accepts carbon instances', function () {
    $a = Period::make(Carbon::make('2018-01-01'), Carbon::make('2018-01-02'));

    $this->assertTrue($a->equals(Period::make('2018-01-01', '2018-01-02')));
});

it('will preserve the time', function () {
    $period = Period::make('2018-01-01 01:02:03', '2018-01-02 04:05:06');

    $this->assertTrue($period->equals(
        Period::make(
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 01:02:03'),
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 04:05:06')
        )
    ));
});

it('will use the start of day when passing strings to a period', function () {
    $period = Period::make('2018-01-01', '2018-01-02');

    $this->assertTrue($period->equals(
        Period::make(
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00'),
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')
        )
    ));
});

it('is iterable', function (int $expectedCount, Period $period) {
    $this->assertSame($expectedCount, iterator_count($period));
})->with('expected_period_lengths');

it('its iterator returns immutable dates', function () {
    $period = Period::make('2018-01-01', '2018-01-15');

    $current = $period->getIterator()->start;

    $this->assertInstanceOf(DateTimeImmutable::class, $current);
});

it('keeps timezone when boundaries are timezoned', function () {
    $timeZone = new DateTimeZone('Europe/London');
    $start = new DateTimeImmutable('2000-01-01', $timeZone);
    $end = new DateTimeImmutable('2000-02-01', $timeZone);
    $period = Period::make($start, $end);
    $this->assertEquals($period->start()->getTimezone(), $timeZone);
    $this->assertEquals($period->end()->getTimezone(), $timeZone);
});

it('gets the correct ceiling of a precision', function (Period $period, ?Precision $precision, Carbon $expected) {
    $this->assertEquals($expected->startOfSecond(), $period->ceilingEnd($precision));
})->with('ceiling_dates');

it('throws if trying to get a ceiling of a lower precision', function () {
    $seconds = Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::HOUR());

    $this->expectException(CannotCeilLowerPrecision::class);
    $this->expectExceptionMessage("Cannot get the latest hour of a second.");

    $seconds->ceilingEnd(Precision::SECOND());
});

it('test from string', function () {
    $this->assertEquals('(2021-01-01 00:00:00,2021-01-22 12:12:12]', Period::fromString('(2021-01-01 00:00:00 , 2021-01-22 12:12:12]')->asString());
    $this->assertEquals('(2021-01-01 00:00,2021-01-22 12:12]', Period::fromString('(2021-01-01 00:00 , 2021-01-22 12:12]')->asString());
    $this->assertEquals('(2021-01-01 00,2021-01-22 12]', Period::fromString('(2021-01-01 00 , 2021-01-22 12]')->asString());
    $this->assertEquals('(2021-01-01,2021-01-22]', Period::fromString('(2021-01-01 , 2021-01-22]')->asString());
    $this->assertEquals('(2021-01,2021-01]', Period::fromString('(2021-01 , 2021-01]')->asString());
    $this->assertEquals('(2021,2021]', Period::fromString('(2021 , 2021]')->asString());
    $this->assertEquals('(2021,2021)', Period::fromString('( 2021 , 2021 )')->asString());
    $this->assertEquals('[2021,2021]', Period::fromString('[ 2021 , 2021 ]')->asString());
});

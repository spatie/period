<?php

use Spatie\Period\Period;
use Spatie\Period\Precision;

/**
 * A    [===============]
 * B        |
 * C  |
 * D                        |
 */
it('can determine whether a period contains a date', function () {
    $period = Period::make('2018-01-01', '2018-01-31');

    $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-01')));
    $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-31')));
    $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-10')));

    $this->assertFalse($period->contains(new DateTimeImmutable('2017-12-31')));
    $this->assertFalse($period->contains(new DateTimeImmutable('2018-02-01')));
});

/**
 * A    [===============]
 * B        [====]
 */
it('contains with other period', function () {
    $period = Period::make('2020-01-01', '2020-01-31');

    $this->assertTrue($period->contains(Period::make('2020-01-10', '2020-01-11')));
    $this->assertTrue($period->contains($period));
    $this->assertFalse($period->contains(Period::make('2020-01-10', '2020-02-11')));
    $this->assertFalse($period->contains(Period::make('2019-01-10', '2020-02-11')));
    $this->assertFalse($period->contains(Period::make('2019-01-10', '2020-01-10')));
});

it('precision is kept when testing contains', function () {
    $a = Period::make('2018-01-01', '2018-01-31', Precision::DAY());

    $this->assertTrue($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00')));
    $this->assertTrue($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')));
    $this->assertTrue($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-31 23:59:59')));

    $this->assertFalse($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-02-01 00:00:00')));
    $this->assertFalse($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2017-12-21 23:59:59')));
});

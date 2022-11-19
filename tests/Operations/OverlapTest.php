<?php

use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

it('can determine if two periods overlap with each other', function (Period $a, Period $b) {
    $this->assertTrue($a->overlapsWith($b));
})->with('overlapping_dates');

/**
 * A        [===========]
 * B            [============]
 *
 * OVERLAP      [=======]
 */
it('can determine an overlap period between two other periods', function () {
    $a = Period::make('2018-01-01', '2018-01-15');

    $b = Period::make('2018-01-10', '2018-01-30');

    $overlapPeriod = Period::make('2018-01-10', '2018-01-15');

    $this->assertTrue($a->overlap($b)->equals($overlapPeriod));
});

/**
 * A       [========]
 * B                   [==]
 * C                           [=====]
 * D              [===============]
 *
 * OVERLAP        [=]   [==]   [==]
 */
it('can determine multiple overlap periods between two other periods', function () {
    $a = Period::make('2018-01-01', '2018-01-31');
    $b = Period::make('2018-02-10', '2018-02-20');
    $c = Period::make('2018-03-01', '2018-03-31');
    $d = Period::make('2018-01-20', '2018-03-10');

    $overlapPeriods = $d->overlapAny($a, $b, $c);

    $this->assertCount(3, $overlapPeriods);

    $this->assertTrue($overlapPeriods[0]->equals(Period::make('2018-01-20', '2018-01-31')));
    $this->assertTrue($overlapPeriods[1]->equals(Period::make('2018-02-10', '2018-02-20')));
    $this->assertTrue($overlapPeriods[2]->equals(Period::make('2018-03-01', '2018-03-10')));
});

/**
 * A              [============]
 * B                   [==]
 * C                   [=======]
 *
 * OVERLAP             [==]
 */
it('can determine the overlap between multiple periods', function () {
    $a = Period::make('2018-01-01', '2018-01-31');
    $b = Period::make('2018-01-10', '2018-01-15');
    $c = Period::make('2018-01-10', '2018-01-31');

    $overlap = $a->overlap($b, $c);

    $this->assertTrue($overlap->equals(Period::make('2018-01-10', '2018-01-15')));
});

/**
 * A              [============]
 * B                                    [==]
 * C                   [=======]
 *
 * OVERLAP             /
 */
it('overlap all returns null when no overlaps', function () {
    $a = Period::make('2018-01-01', '2018-02-01');
    $b = Period::make('2018-05-10', '2018-06-01');
    $c = Period::make('2018-01-10', '2018-02-01');

    $overlap = $a->overlap($b, $c);

    $this->assertNull($overlap);
});

it('non overlapping dates return an empty collection', function () {
    $a = Period::make('2019-01-01', '2019-01-31');
    $b = Period::make('2019-02-01', '2019-02-28');

    $this->assertTrue($a->overlapAny($b)->isEmpty());
});

it('can determine that two periods do not overlap', function () {
    $a = Period::make('2018-01-05', '2018-01-10');
    $b = Period::make('2018-01-22', '2018-01-30');

    $overlap = $a->overlap($b);

    $this->assertNull($overlap);
});

it('can determine that two periods do not overlap with each other', function (Period $a, Period $b) {
    $this->assertFalse($a->overlapsWith($b));
})->with('no_overlapping_dates');

it('passing empty period collection returns null', function () {
    $current = Period::make('2018-01-01', '2018-01-31');
    $emptyCollection = new PeriodCollection();

    $diff = $current->overlap(...$emptyCollection);

    $this->assertNull($diff);
});

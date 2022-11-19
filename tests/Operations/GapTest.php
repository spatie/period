<?php

use Spatie\Period\Period;

/**
 * A    [===]
 * B            [===]
 *
 * GAP       [=]
 */
it('can determine the gap between two periods', function () {
    $a = Period::make('2018-01-01', '2018-01-10');

    $b = Period::make('2018-01-15', '2018-01-31');

    $gap = $a->gap($b);

    $this->assertTrue($gap->equals(Period::make('2018-01-11', '2018-01-14')));
});

/**
 * A            [===]
 * B    [===]
 *
 * GAP       [=]
 */
it('can still determine the gap between two periods even when the periods are not in order', function () {
    $a = Period::make('2018-01-15', '2018-01-31');

    $b = Period::make('2018-01-01', '2018-01-10');

    $gap = $a->gap($b);

    $this->assertTrue($gap->equals(Period::make('2018-01-11', '2018-01-14')));
});

/**
 * A           [=====]
 * B    [=====]
 *
 * GAP
 */
it('will determine that there is no gap if the periods only touch but do not overlap', function () {
    $a = Period::make('2018-01-15', '2018-01-31');

    $b = Period::make('2018-02-01', '2018-02-01');

    $gap = $a->gap($b);

    $this->assertNull($gap);
});

/**
 * A           [=====]
 * B       [=====]
 *
 * GAP
 */
it('will determine that there is no gap when periods overlap', function () {
    $a = Period::make('2018-01-15', '2018-01-31');

    $b = Period::make('2018-01-28', '2018-02-01');

    $gap = $a->gap($b);

    $this->assertNull($gap);
});

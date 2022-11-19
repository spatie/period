<?php

use Carbon\Carbon;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

dataset('overlapping_dates', function () {
    /*
     * A    [=====]
     * B       [=====]
     */
    yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-15', '2018-02-15')];

    /*
     * A        [=====]
     * B    [=============]
     */
    yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-01-01', '2019-01-01')];

    /*
     * A        [=====]
     * B     [=====]
     */
    yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-12-01', '2018-01-15')];

    /*
     * A    [=============]
     * B        [=====]
     */
    yield [Period::make('2017-01-01', '2019-01-01'), Period::make('2018-01-01', '2018-02-01')];

    /*
     * A    [====]
     * B    [====]
     */
    yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-01', '2018-02-01')];
});

dataset('no_overlapping_dates', function () {
    /*
     * A    [===]
     * B          [===]
     */
    yield [Period::make('2018-01-01', '2018-01-31'), Period::make('2018-02-01', '2018-02-28')];

    /*
     * A          [===]
     * B    [===]
     */
    yield [Period::make('2018-02-01', '2018-02-28'), Period::make('2018-01-01', '2018-01-31')];
});

dataset('periods_with_amounts_of_included_dates', function () {
    yield [4, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_NONE())];
    yield [3, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_START())];
    yield [3, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_END())];
    yield [2, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_ALL())];

    yield [43, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_NONE())];
    yield [42, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_START())];
    yield [42, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_END())];
    yield [41, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_ALL())];

    yield [31, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_NONE())];
    yield [30, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_START())];
    yield [30, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_END())];
    yield [29, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_ALL())];

    yield [24, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_NONE())];
    yield [23, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_START())];
    yield [23, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_END())];
    yield [22, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_ALL())];

    yield [1440, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_NONE())];
    yield [1439, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_START())];
    yield [1439, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_END())];
    yield [1438, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_ALL())];

    yield [86363, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_NONE())];
    yield [86362, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_START())];
    yield [86362, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_END())];
    yield [86361, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_ALL())];
});

dataset('ceiling_dates', function () {
    // Test inclusive Period's at same precision
    yield [
        Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::SECOND()),
        Precision::SECOND(),
        Carbon::make('2018-01-15 11:30:15')->endOfSecond(),
    ];

    yield [
        Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::MINUTE()),
        Precision::MINUTE(),
        Carbon::make('2018-01-15 11:30:15')->endOfMinute(),
    ];

    yield [
        Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::HOUR()),
        Precision::HOUR(),
        Carbon::make('2018-01-15 11:30:15')->endOfHour(),
    ];

    yield [
        Period::make('2018-01-01', '2018-01-15', Precision::DAY()),
        Precision::DAY(),
        Carbon::make('2018-01-15')->endOfDay(),
    ];

    yield [
        Period::make('2018-01-01', '2018-01-15', Precision::MONTH()),
        Precision::MONTH(),
        Carbon::make('2018-01-15')->endOfMonth(),
    ];

    yield [
        Period::make('2018-01-01', '2018-01-15', Precision::YEAR()),
        Precision::YEAR(),
        Carbon::make('2018-01-15')->endOfYear(),
    ];

    // Test defaults to same precision as period
    yield [
        Period::make('2018-01-01', '2018-01-15', Precision::MONTH()),
        null,
        Carbon::make('2018-01-15')->endOfMonth(),
    ];

    // Test higher precision
    yield [
        Period::make('2018-01-01', '2018-01-15', Precision::DAY()),
        Precision::MONTH(),
        Carbon::make('2018-01-15')->endOfMonth(),
    ];

    // Test exclusive period
    yield [
        Period::make('2018-01-01', '2018-01-15', Precision::DAY(), Boundaries::EXCLUDE_END()),
        Precision::DAY(),
        Carbon::make('2018-01-15')->subDay()->endOfDay(),
    ];
});

dataset('rounding_dates', function () {
    return [
        [Precision::YEAR(), '2018-01-01 00:00:00', '2018-01-01 00:00:00'],
        [Precision::MONTH(), '2018-02-01 00:00:00', '2018-03-01 00:00:00'],
        [Precision::DAY(), '2018-02-05 00:00:00', '2018-03-05 00:00:00'],
        [Precision::HOUR(), '2018-02-05 11:00:00', '2018-03-05 11:00:00'],
        [Precision::MINUTE(), '2018-02-05 11:11:00', '2018-03-05 11:11:00'],
        [Precision::SECOND(), '2018-02-05 11:11:11', '2018-03-05 11:11:11'],
    ];
});

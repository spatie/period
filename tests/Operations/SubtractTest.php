<?php

use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

/**
 * A      [=====]
 * B                [=====]
 *
 * RES    [=====]
 */
it('subtraction without overlap', function () {
    $a = Period::make('2020-01-01', '2020-01-02');
    $b = Period::make('2020-02-01', '2020-02-02');

    $result = $a->subtract($b);

    expect($result)->toHaveCount(1);

    expect($result[0]->equals($a))->toBeTrue();
});

/**
 * A        [=======]
 * B            [=======]
 *
 * RES     [==]
 */
it('subtraction right', function () {
    $a = Period::make('2020-01-01', '2020-01-31');
    $b = Period::make('2020-01-11', '2020-01-31');

    $result = $a->subtract($b);

    expect($result)->toHaveCount(1);
    expect($result[0]->equals(Period::make('2020-01-01', '2020-01-10')))->toBeTrue();
});

/**
 * A            [=======]
 * B        [=======]
 *
 * RES               [==]
 */
it('subtraction left', function () {
    $a = Period::make('2020-01-01', '2020-01-31');
    $b = Period::make('2020-01-01', '2020-01-09');

    $result = $a->subtract($b);

    expect($result)->toHaveCount(1);
    expect($result[0]->equals(Period::make('2020-01-10', '2020-01-31')))->toBeTrue();
});

/**
 * A        [=================]
 * B             [=======]
 *
 * RES      [==]          [==]
 */
it('subtraction left and right', function () {
    $a = Period::make('2020-01-01', '2020-01-31');
    $b = Period::make('2020-01-11', '2020-01-14');

    $result = $a->subtract($b);

    expect($result)->toHaveCount(2);
    expect($result[0]->equals(Period::make('2020-01-01', '2020-01-10')))->toBeTrue();
    expect($result[1]->equals(Period::make('2020-01-15', '2020-01-31')))->toBeTrue();
});

/**
 * A             [=======]
 * B        [=================]
 *
 * RES      [==]          [==]
 */
it('subtraction full', function () {
    $a = Period::make('2020-01-11', '2020-01-14');
    $b = Period::make('2020-01-01', '2020-01-31');

    $result = $a->subtract($b);

    expect($result)->toHaveCount(0);
});

/**
 * CURRENT         [===========]
 *
 * A       [=========]
 * B                     [==]
 * C                      [=========]
 *
 * RESULT             [=]
 */
it('subtraction many', function () {
    $current = Period::make('2018-01-20', '2018-03-15');

    $a = Period::make('2018-01-01', '2018-01-31');
    $b = Period::make('2018-02-10', '2018-02-20');
    $c = Period::make('2018-02-11', '2018-03-31');

    $diff = $current->subtract($a, $b, $c);

    expect($diff)->toHaveCount(1);

    expect($diff[0]->equals(Period::make('2018-02-01', '2018-02-09')))->toBeTrue();
});

/**
 * A                       [========]
 * B             [=========]
 * CURRENT         [============]
 *
 * OVERLAP         [============]
 * DIFF
 */
it('if all periods overlap it will determine that there is no diff', function () {
    $a = Period::make('2018-01-15', '2018-02-10');
    $b = Period::make('2017-12-20', '2018-01-15');

    $current = Period::make('2018-01-01', '2018-01-31');

    $diff = $current->subtract($a, $b);

    expect($diff)->toHaveCount(0);
});

/**
 * CURRENT         [=======]
 *
 * A                            [========]
 *
 * DIFF             [=======]
 */
it('can subtract', function () {
    $a = Period::make('2018-02-15', '2018-02-20');

    $current = Period::make('2018-01-01', '2018-01-31');

    $diff = $current->subtract($a);

    expect($diff)->toHaveCount(1);
    expect($diff[0]->equals($current))->toBeTrue();
});

/**
 * A                   [====]
 * B                               [========]
 * C         [=====]
 * CURRENT      [========================]
 *
 * DIFF             [=]      [====]
 */
it('can determine multiple diffs', function () {
    $a = Period::make('2018-01-05', '2018-01-10');
    $b = Period::make('2018-01-15', '2018-03-01');
    $c = Period::make('2017-01-01', '2018-01-02');

    $current = Period::make('2018-01-01', '2018-01-31');

    $diff = $current->subtract($a, $b, $c);

    expect($diff)->toHaveCount(2);

    expect($diff[0]->equals(Period::make('2018-01-03', '2018-01-04')))->toBeTrue();
    expect($diff[1]->equals(Period::make('2018-01-11', '2018-01-14')))->toBeTrue();
});

/**
 * CURRENT  [=============================]
 *
 * A                            [====]
 * B                [====]
 *
 * DIFF     [======]      [====]      [===]
 */
it('can determine multiple diffs for sure', function () {
    $current = Period::make('2018-01-01', '2018-01-31');

    $a = Period::make('2018-01-15', '2018-01-20');
    $b = Period::make('2018-01-05', '2018-01-10');

    $diff = $current->subtract($a, $b);

    expect($diff)->toHaveCount(3);

    expect($diff[0]->equals(Period::make('2018-01-01', '2018-01-04')))->toBeTrue();
    expect($diff[1]->equals(Period::make('2018-01-11', '2018-01-14')))->toBeTrue();
    expect($diff[2]->equals(Period::make('2018-01-21', '2018-01-31')))->toBeTrue();
});

it('passing empty period collection returns same period within collection', function () {
    $current = Period::make('2018-01-01', '2018-01-31');
    $emptyCollection = new PeriodCollection();

    $diff = $current->subtract(...$emptyCollection);

    expect($diff)->toBeInstanceOf(PeriodCollection::class);
    expect($diff)->toHaveCount(1);
    expect($diff[0]->equals($current))->toBeTrue();
});

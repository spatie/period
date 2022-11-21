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

    expect($period->contains(new DateTimeImmutable('2018-01-01')))->toBeTrue();
    expect($period->contains(new DateTimeImmutable('2018-01-31')))->toBeTrue();
    expect($period->contains(new DateTimeImmutable('2018-01-10')))->toBeTrue();

    expect($period->contains(new DateTimeImmutable('2017-12-31')))->toBeFalse();
    expect($period->contains(new DateTimeImmutable('2018-02-01')))->toBeFalse();
});

/**
 * A    [===============]
 * B        [====]
 */
it('contains with other period', function () {
    $period = Period::make('2020-01-01', '2020-01-31');

    expect($period->contains(Period::make('2020-01-10', '2020-01-11')))->toBeTrue();
    expect($period->contains($period))->toBeTrue();
    expect($period->contains(Period::make('2020-01-10', '2020-02-11')))->toBeFalse();
    expect($period->contains(Period::make('2019-01-10', '2020-02-11')))->toBeFalse();
    expect($period->contains(Period::make('2019-01-10', '2020-01-10')))->toBeFalse();
});

it('precision is kept when testing contains', function () {
    $a = Period::make('2018-01-01', '2018-01-31', Precision::DAY());

    expect($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00')))->toBeTrue();
    expect($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')))->toBeTrue();
    expect($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-31 23:59:59')))->toBeTrue();

    expect($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-02-01 00:00:00')))->toBeFalse();
    expect($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2017-12-21 23:59:59')))->toBeFalse();
});

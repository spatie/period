<?php

use Spatie\Period\Exceptions\CannotComparePeriods;
use Spatie\Period\Period;
use Spatie\Period\Precision;

it('dates are rounded on precision', function (Precision $precision, string $expectedStart, string $expectedEnd) {
    $period = Period::make('2018-02-05 11:11:11', '2018-03-05 11:11:11', $precision);

    expect($period->start())->toEqual(DateTime::createFromFormat('Y-m-d H:i:s', $expectedStart));
    expect($period->end())->toEqual(DateTime::createFromFormat('Y-m-d H:i:s', $expectedEnd));
})->with('rounding_dates');

it('comparing two periods with different precision is not allowed', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::MONTH());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::DAY());

    $a->overlapsWith($b);
})->throws(CannotComparePeriods::class);

it('precision with seconds', function () {
    $a = Period::make('2018-01-01 00:00:15', '2018-01-01 00:00:15', Precision::SECOND());
    $b = Period::make('2018-01-01 00:00:15', '2018-01-01 00:00:15', Precision::SECOND());
    $c = Period::make('2018-01-01 00:00:16', '2018-01-01 00:00:16', Precision::SECOND());

    expect($a->overlapsWith($b))->toBeTrue();
    expect($a->overlapsWith($c))->toBeFalse();
});

it('precision with minutes', function () {
    $a = Period::make('2018-01-01 00:15:00', '2018-01-01 00:15:00', Precision::MINUTE());
    $b = Period::make('2018-01-01 00:15:00', '2018-01-01 00:15:00', Precision::MINUTE());
    $c = Period::make('2018-01-01 00:16:00', '2018-01-01 00:16:00', Precision::MINUTE());

    expect($a->overlapsWith($b))->toBeTrue();
    expect($a->overlapsWith($c))->toBeFalse();
});

it('precision with hours', function () {
    $a = Period::make('2018-01-01 15:00:00', '2018-01-01 15:00:00', Precision::HOUR());
    $b = Period::make('2018-01-01 15:00:00', '2018-01-01 15:00:00', Precision::HOUR());
    $c = Period::make('2018-01-01 16:00:00', '2018-01-01 16:00:00', Precision::HOUR());

    expect($a->overlapsWith($b))->toBeTrue();
    expect($a->overlapsWith($c))->toBeFalse();
});

it('precision with days', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::DAY());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::DAY());
    $c = Period::make('2018-01-02', '2018-01-02', Precision::DAY());

    expect($a->overlapsWith($b))->toBeTrue();
    expect($a->overlapsWith($c))->toBeFalse();
});

it('precision with months', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::MONTH());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::MONTH());
    $c = Period::make('2018-02-01', '2018-02-01', Precision::MONTH());

    expect($a->overlapsWith($b))->toBeTrue();
    expect($a->overlapsWith($c))->toBeFalse();
});

it('precision with years', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::YEAR());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::YEAR());
    $c = Period::make('2019-01-01', '2019-01-01', Precision::YEAR());

    expect($a->overlapsWith($b))->toBeTrue();
    expect($a->overlapsWith($c))->toBeFalse();
});

it('precision is kept when comparing with the ranges start', function () {
    $a = Period::make('2018-01-01 11:11:11', '2018-01-31', Precision::DAY());

    $boundaryDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 11:11:11');

    expect($a->startsAt($boundaryDate))->toBeTrue();

    $includedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00');

    expect($a->startsBefore($includedDate))->toBeTrue();
    expect($a->startsBeforeOrAt($includedDate))->toBeTrue();
    expect($a->startsAfter($includedDate))->toBeFalse();
    expect($a->startsAfterOrAt($includedDate))->toBeFalse();

    $excludedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2017-12-31 23:59:59');

    expect($a->startsBefore($excludedDate))->toBeFalse();
    expect($a->startsBeforeOrAt($excludedDate))->toBeFalse();
    expect($a->startsAfter($excludedDate))->toBeTrue();
    expect($a->startsAfterOrAt($excludedDate))->toBeTrue();
});

it('precision is kept when comparing with the ranges end', function () {
    $a = Period::make('2018-01-01', '2018-01-31', Precision::DAY());

    $boundaryDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-31 23:59:59');

    expect($a->endsAt($boundaryDate))->toBeTrue();

    $includedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-30 23:59:59');

    expect($a->endsAfter($includedDate))->toBeTrue();
    expect($a->endsAfterOrAt($includedDate))->toBeTrue();
    expect($a->endsBefore($includedDate))->toBeFalse();
    expect($a->endsBeforeOrAt($includedDate))->toBeFalse();

    $excludedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-02-01 00:00:00');

    expect($a->endsBefore($excludedDate))->toBeTrue();
    expect($a->endsBeforeOrAt($excludedDate))->toBeTrue();
    expect($a->endsAfter($excludedDate))->toBeFalse();
    expect($a->endsAfterOrAt($excludedDate))->toBeFalse();
});

it('precision is kept with subtract', function () {
    $a = Period::make('2018-01-05 00:00:00', '2018-01-10 00:00:00', Precision::MINUTE());
    $b = Period::make('2018-01-15 00:00:00', '2018-03-01 00:00:00', Precision::MINUTE());

    [$diff] = $a->subtract($b);

    expect($diff->precision())->toEqual(Precision::MINUTE());
});

it('precision is kept with overlap', function () {
    $a = Period::make('2018-01-05 00:00:00', '2018-01-10 00:00:00', Precision::MINUTE());
    $b = Period::make('2018-01-01 00:00:00', '2018-01-31 00:00:00', Precision::MINUTE());

    [$diff] = $a->overlapAny($b);

    expect($diff->precision())->toEqual(Precision::MINUTE());
});

it('precision is kept with gap', function () {
    $a = Period::make('2018-01-05 00:00:00', '2018-01-10 00:00:00', Precision::MINUTE());
    $b = Period::make('2018-01-15 00:00:00', '2018-01-31 00:00:00', Precision::MINUTE());

    $gap = $a->gap($b);

    expect($gap->precision())->toEqual(Precision::MINUTE());
});

it('precision seconds is more precise than hours', function () {
    $hours = Precision::HOUR();
    $seconds = Precision::SECOND();

    expect($seconds->higherThan($hours))->toBeTrue();
});

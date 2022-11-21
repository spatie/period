<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Period;

it('exclude none', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_NONE());

    expect($period->isStartExcluded())->toBeFalse();
    expect($period->isEndExcluded())->toBeFalse();
});

it('exclude start', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_START());

    expect($period->isStartExcluded())->toBeTrue();
    expect($period->isEndExcluded())->toBeFalse();
});

it('exclude end', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_END());

    expect($period->isStartExcluded())->toBeFalse();
    expect($period->isEndExcluded())->toBeTrue();
});

it('exclude all', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_ALL());

    expect($period->isStartExcluded())->toBeTrue();
    expect($period->isEndExcluded())->toBeTrue();
});

it('length with boundaries', function ($expectedAmount, Period $period) {
    expect($period->length())->toEqual($expectedAmount);
})->with('periods_with_amounts_of_included_dates');

it('overlap with excluded boundaries', function () {
    $a = Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END());
    $b = Period::make('2018-01-05', '2018-01-10');
    expect($a->overlapsWith($b))->toBeFalse();

    $a = Period::make('2018-01-01', '2018-01-05');
    $b = Period::make('2018-01-05', '2018-01-10', boundaries: Boundaries::EXCLUDE_START());
    expect($a->overlapsWith($b))->toBeFalse();

    $a = Period::make('2018-01-01', '2018-01-05');
    $b = Period::make('2018-01-05', '2018-01-10');
    expect($a->overlapsWith($b))->toBeTrue();
});

it('subtract with boundaries', function (string $period, string $subtract, string $result) {
    expect(Period::fromString($period)->subtract(Period::fromString($subtract))[0]->asString())->toEqual($result);
})->with('boundaries_for_subtract');

it('overlap with boundaries', function (string $period, string $overlap, string $result) {
    expect(Period::fromString($period)->overlap(Period::fromString($overlap))->asString())->toEqual($result);
})->with('boundaries_for_overlap');

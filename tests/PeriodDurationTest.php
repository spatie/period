<?php

declare(strict_types=1);

use Spatie\Period\Period;
use Spatie\Period\PeriodDuration;

it('is the same as', function (Period $a, Period $b) {
    $duration = new PeriodDuration($a);
    $other = new PeriodDuration($b);

    expect($duration->equals($other))->toBeTrue();
    expect($duration->isLargerThan($other))->toBeFalse();
    expect($duration->isSmallerThan($other))->toBeFalse();
    expect($duration->compareTo($other))->toBe(0);
})->with('sames');

it('is equal but not the same', function (Period $a, Period $b) {
    $duration = new PeriodDuration($a);
    $other = new PeriodDuration($b);

    expect($duration->equals($other))->toBeTrue();
    expect($duration->isLargerThan($other))->toBeFalse();
    expect($duration->isSmallerThan($other))->toBeFalse();
    expect($duration->compareTo($other))->toBe(0);
})->with('equals');

it('is different', function (Period $a, Period $b) {
    $duration = new PeriodDuration($a);
    $other = new PeriodDuration($b);

    expect($duration->equals($other))->toBeFalse();
})->with('differents');

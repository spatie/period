<?php

declare(strict_types=1);

use Spatie\Period\Period;
use Spatie\Period\PeriodDuration;

it('is the same as', function (Period $a, Period $b) {
    $duration = new PeriodDuration($a);
    $other = new PeriodDuration($b);

    $this->assertTrue($duration->equals($other));
    $this->assertFalse($duration->isLargerThan($other));
    $this->assertFalse($duration->isSmallerThan($other));
    $this->assertSame(0, $duration->compareTo($other));
})->with('sames');

it('is equal but not the same', function (Period $a, Period $b) {
    $duration = new PeriodDuration($a);
    $other = new PeriodDuration($b);

    $this->assertTrue($duration->equals($other));
    $this->assertFalse($duration->isLargerThan($other));
    $this->assertFalse($duration->isSmallerThan($other));
    $this->assertSame(0, $duration->compareTo($other));
})->with('equals');

it('is different', function (Period $a, Period $b) {
    $duration = new PeriodDuration($a);
    $other = new PeriodDuration($b);

    $this->assertFalse($duration->equals($other));
})->with('differents');

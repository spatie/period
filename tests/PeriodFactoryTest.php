<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodFactory;
use Spatie\Period\Precision;

it('make with boundaries', function () {
    $period = PeriodFactory::makeWithBoundaries(
        Period::class,
        DateTimeImmutable::createFromFormat('Y-m-d', '2021-01-01'),
        DateTimeImmutable::createFromFormat('Y-m-d', '2021-01-20'),
        Precision::DAY(),
        Boundaries::EXCLUDE_END()
    );

    $this->assertTrue(Period::fromString('[2021-01-01,2021-01-21)')->equals($period));
});

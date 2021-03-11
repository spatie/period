<?php

namespace Spatie\Period\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodFactory;
use Spatie\Period\Precision;

class PeriodFactoryTest extends TestCase
{
    /** @test */
    public function make_with_boundaries()
    {
        $period = PeriodFactory::makeWithBoundaries(
            Period::class,
            DateTimeImmutable::createFromFormat('Y-m-d', '2021-01-01'),
            DateTimeImmutable::createFromFormat('Y-m-d', '2021-01-20'),
            Precision::DAY(),
            Boundaries::EXCLUDE_END()
        );

        $this->assertTrue(Period::fromString('[2021-01-01,2021-01-21)')->equals($period));
    }
}

<?php

namespace Spatie\Period\Tests;

use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;

class BoundaryTest extends TestCase
{
    /** @test */
    public function exclude_none()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_NONE);

        $this->assertFalse($period->startExcluded());
        $this->assertFalse($period->endExcluded());
    }

    /** @test */
    public function exclude_start()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_START);

        $this->assertTrue($period->startExcluded());
        $this->assertFalse($period->endExcluded());
    }

    /** @test */
    public function exclude_end()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_END);

        $this->assertFalse($period->startExcluded());
        $this->assertTrue($period->endExcluded());
    }

    /** @test */
    public function exclude_all()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_ALL);

        $this->assertTrue($period->startExcluded());
        $this->assertTrue($period->endExcluded());
    }
}

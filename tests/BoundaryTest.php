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

    /** @test */
    public function length_with_boundaries()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_START);
        $this->assertEquals(30, $period->length());

        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_END);
        $this->assertEquals(30, $period->length());

        $period = Period::make('2018-01-01', '2018-01-31', null, Period::EXCLUDE_ALL);
        $this->assertEquals(29, $period->length());
    }

    /** @test */
    public function overlap_with_excluded_boundary()
    {
        $a = Period::make('2018-01-01', '2018-01-05', null, Period::EXCLUDE_END);
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10', null, Period::EXCLUDE_START);
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertTrue($a->overlapsWith($b));
    }
}

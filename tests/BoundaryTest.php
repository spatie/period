<?php

namespace Spatie\Period\Tests;

use Spatie\Period\Period;
use Spatie\Period\Boundaries;
use PHPUnit\Framework\TestCase;

class BoundaryTest extends TestCase
{
    /** @test */
    public function exclude_none()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_NONE);

        $this->assertFalse($period->startExcluded());
        $this->assertFalse($period->endExcluded());
    }

    /** @test */
    public function exclude_start()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_START);

        $this->assertTrue($period->startExcluded());
        $this->assertFalse($period->endExcluded());
    }

    /** @test */
    public function exclude_end()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_END);

        $this->assertFalse($period->startExcluded());
        $this->assertTrue($period->endExcluded());
    }

    /** @test */
    public function exclude_all()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_ALL);

        $this->assertTrue($period->startExcluded());
        $this->assertTrue($period->endExcluded());
    }

    /** @test */
    public function length_with_boundaries()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_START);
        $this->assertEquals(30, $period->length());

        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_END);
        $this->assertEquals(30, $period->length());

        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_ALL);
        $this->assertEquals(29, $period->length());
    }

    /** @test */
    public function overlap_with_excluded_boundaries()
    {
        $a = Period::make('2018-01-01', '2018-01-05', null, Boundaries::EXCLUDE_END);
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10', null, Boundaries::EXCLUDE_START);
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertTrue($a->overlapsWith($b));
    }
}

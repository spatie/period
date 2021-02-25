<?php

namespace Spatie\Period\Tests\Operations;

use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;

class GapTest extends TestCase
{
    /**
     * @test
     *
     * A    [===]
     * B            [===]
     *
     * GAP       [=]
     */
    public function it_can_determine_the_gap_between_two_periods()
    {
        $a = Period::make('2018-01-01', '2018-01-10');

        $b = Period::make('2018-01-15', '2018-01-31');

        $gap = $a->gap($b);

        $this->assertTrue($gap->equals(Period::make('2018-01-11', '2018-01-14')));
    }

    /**
     * @test
     *
     * A            [===]
     * B    [===]
     *
     * GAP       [=]
     */
    public function it_can_still_determine_the_gap_between_two_periods_even_when_the_periods_are_not_in_order()
    {
        $a = Period::make('2018-01-15', '2018-01-31');

        $b = Period::make('2018-01-01', '2018-01-10');

        $gap = $a->gap($b);

        $this->assertTrue($gap->equals(Period::make('2018-01-11', '2018-01-14')));
    }

    /**
     * @test
     *
     * A           [=====]
     * B    [=====]
     *
     * GAP
     */
    public function it_will_determine_that_there_is_no_gap_if_the_periods_only_touch_but_do_not_overlap()
    {
        $a = Period::make('2018-01-15', '2018-01-31');

        $b = Period::make('2018-02-01', '2018-02-01');

        $gap = $a->gap($b);

        $this->assertNull($gap);
    }

    /**
     * @test
     *
     * A           [=====]
     * B       [=====]
     *
     * GAP
     */
    public function it_will_determine_that_there_is_no_gap_when_periods_overlap()
    {
        $a = Period::make('2018-01-15', '2018-01-31');

        $b = Period::make('2018-01-28', '2018-02-01');

        $gap = $a->gap($b);

        $this->assertNull($gap);
    }
}

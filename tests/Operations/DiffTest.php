<?php

namespace Spatie\Period\Tests\Operations;

use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;

class DiffTest extends TestCase
{
    /**
     * @test
     *
     * A        [===========]
     * B            [===========]
     *
     * DIFF     [==]         [==]
     */
    public function it_can_create_a_diff_for_two_periods()
    {
        $a = Period::make('2018-01-01', '2018-01-15');

        $b = Period::make('2018-01-10', '2018-01-30');

        $diffs = $a->diff($b);

        $this->assertTrue($diffs[0]->equals(Period::make('2018-01-01', '2018-01-09')));
        $this->assertTrue($diffs[1]->equals(Period::make('2018-01-16', '2018-01-30')));
    }

    /**
     * @test
     *
     * A             [==========]
     * B        [============]
     *
     * DIFF     [==]          [==]
     */
    public function it_can_still_create_a_diff_for_two_periods_even_if_there_are_not_ordered()
    {
        $a = Period::make('2018-01-10', '2018-01-30');

        $b = Period::make('2018-01-01', '2018-01-15');

        $diffs = $a->diff($b);

        $this->assertTrue($diffs[0]->equals(Period::make('2018-01-01', '2018-01-09')));
        $this->assertTrue($diffs[1]->equals(Period::make('2018-01-16', '2018-01-30')));
    }

    /**
     * @test
     *
     * A        [=============================]
     * B            [========]
     *
     * DIFF     [==]          [===============]
     */
    public function diff_with_one_period_within()
    {
        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-01-10', '2018-01-15');

        $diff = $a->diff($b);

        $this->assertCount(2, $diff);
    }
}

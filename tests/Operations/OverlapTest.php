<?php

namespace Spatie\Period\Tests\Operations;

use Generator;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

class OverlapTest extends TestCase
{
    /**
     * @test
     * @dataProvider overlappingDates
     */
    public function it_can_determine_if_two_periods_overlap_with_each_other(Period $a, Period $b)
    {
        $this->assertTrue($a->overlapsWith($b));
    }

    public function overlappingDates(): Generator
    {
        /*
         * A    [=====]
         * B       [=====]
         */
        yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-15', '2018-02-15')];

        /*
         * A        [=====]
         * B    [=============]
         */
        yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-01-01', '2019-01-01')];

        /*
         * A        [=====]
         * B     [=====]
         */
        yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-12-01', '2018-01-15')];

        /*
         * A    [=============]
         * B        [=====]
         */
        yield [Period::make('2017-01-01', '2019-01-01'), Period::make('2018-01-01', '2018-02-01')];

        /*
         * A    [====]
         * B    [====]
         */
        yield [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-01', '2018-02-01')];
    }

    /**
     * @test
     *
     * A        [===========]
     * B            [============]
     *
     * OVERLAP      [=======]
     */
    public function it_can_determine_an_overlap_period_between_two_other_periods()
    {
        $a = Period::make('2018-01-01', '2018-01-15');

        $b = Period::make('2018-01-10', '2018-01-30');

        $overlapPeriod = Period::make('2018-01-10', '2018-01-15');

        $this->assertTrue($a->overlap($b)->equals($overlapPeriod));
    }

    /**
     * @test
     *
     * A       [========]
     * B                   [==]
     * C                           [=====]
     * D              [===============]
     *
     * OVERLAP        [=]   [==]   [==]
     */
    public function it_can_determine_multiple_overlap_periods_between_two_other_periods()
    {
        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-02-10', '2018-02-20');
        $c = Period::make('2018-03-01', '2018-03-31');
        $d = Period::make('2018-01-20', '2018-03-10');

        $overlapPeriods = $d->overlapAny($a, $b, $c);

        $this->assertCount(3, $overlapPeriods);

        $this->assertTrue($overlapPeriods[0]->equals(Period::make('2018-01-20', '2018-01-31')));
        $this->assertTrue($overlapPeriods[1]->equals(Period::make('2018-02-10', '2018-02-20')));
        $this->assertTrue($overlapPeriods[2]->equals(Period::make('2018-03-01', '2018-03-10')));
    }

    /**
     * @test
     *
     * A              [============]
     * B                   [==]
     * C                   [=======]
     *
     * OVERLAP             [==]
     */
    public function it_can_determine_the_overlap_between_multiple_periods()
    {
        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-01-10', '2018-01-15');
        $c = Period::make('2018-01-10', '2018-01-31');

        $overlap = $a->overlap($b, $c);

        $this->assertTrue($overlap->equals(Period::make('2018-01-10', '2018-01-15')));
    }

    /**
     * @test
     *
     * A              [============]
     * B                                    [==]
     * C                   [=======]
     *
     * OVERLAP             /
     */
    public function overlap_all_returns_null_when_no_overlaps()
    {
        $a = Period::make('2018-01-01', '2018-02-01');
        $b = Period::make('2018-05-10', '2018-06-01');
        $c = Period::make('2018-01-10', '2018-02-01');

        $overlap = $a->overlap($b, $c);

        $this->assertNull($overlap);
    }

    /** @test */
    public function non_overlapping_dates_return_an_empty_collection()
    {
        $a = Period::make('2019-01-01', '2019-01-31');
        $b = Period::make('2019-02-01', '2019-02-28');

        $this->assertTrue($a->overlapAny($b)->isEmpty());
    }

    /** @test */
    public function it_can_determine_that_two_periods_do_not_overlap()
    {
        $a = Period::make('2018-01-05', '2018-01-10');
        $b = Period::make('2018-01-22', '2018-01-30');

        $overlap = $a->overlap($b);

        $this->assertNull($overlap);
    }

    /**
     * @test
     * @dataProvider noOverlappingDates
     */
    public function it_can_determine_that_two_periods_do_not_overlap_with_each_other(Period $a, Period $b)
    {
        $this->assertFalse($a->overlapsWith($b));
    }

    public function noOverlappingDates()
    {
        /*
         * A    [===]
         * B          [===]
         */
        yield [Period::make('2018-01-01', '2018-01-31'), Period::make('2018-02-01', '2018-02-28')];

        /*
         * A          [===]
         * B    [===]
         */
        yield [Period::make('2018-02-01', '2018-02-28'), Period::make('2018-01-01', '2018-01-31')];
    }

    /** @test */
    public function passing_empty_period_collection_returns_null()
    {
        $current = Period::make('2018-01-01', '2018-01-31');
        $emptyCollection = new PeriodCollection;

        $diff = $current->overlap(...$emptyCollection);

        $this->assertNull($diff);
    }
}

<?php

namespace Spatie\Period\Tests;

use Carbon\Carbon;
use DateTimeImmutable;
use Spatie\Period\Period;
use Spatie\Period\Precision;
use Spatie\Period\Boundaries;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_period_length()
    {
        $period = Period::make('2018-01-01', '2018-01-15');

        $this->assertEquals(15, $period->length());
    }

    /** @test */
    public function it_has_a_duration()
    {
        $a = Period::make('2018-01-01', '2018-01-15');
        $b = Period::make('2018-02-01', '2018-02-15');

        $this->assertTrue($a->duration()->equals($b->duration()));
    }

    /**
     * @test
     * @dataProvider overlappingDates
     */
    public function it_can_determine_if_two_periods_overlap_with_each_other(Period $a, Period $b)
    {
        $this->assertTrue($a->overlapsWith($b));
    }

    /** @test */
    public function it_can_determine_if_two_periods_touch_each_other()
    {
        $this->assertTrue(
            Period::make('2018-01-01', '2018-01-01')
                ->touchesWith(Period::make('2018-01-02', '2018-01-02'))
        );

        $this->assertTrue(
            Period::make('2018-01-02', '2018-01-02')
                ->touchesWith(Period::make('2018-01-01', '2018-01-01'))
        );

        $this->assertFalse(
            Period::make('2018-01-01', '2018-01-01')
                ->touchesWith(Period::make('2018-01-03', '2018-01-03'))
        );

        $this->assertFalse(
            Period::make('2018-01-03', '2018-01-03')
                ->touchesWith(Period::make('2018-01-01', '2018-01-01'))
        );
    }

    /**
     * @test
     * @dataProvider noOverlappingDates
     */
    public function it_can_determine_that_two_periods_do_not_overlap_with_each_other(Period $a, Period $b)
    {
        $this->assertFalse($a->overlapsWith($b));
    }

    public function overlappingDates(): array
    {
        return [
            /*
             * A    [=====]
             * B       [=====]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-15', '2018-02-15')],

            /*
             * A        [=====]
             * B    [=============]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-01-01', '2019-01-01')],

            /*
             * A        [=====]
             * B     [=====]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-12-01', '2018-01-15')],

            /*
             * A    [=============]
             * B        [=====]
             */
            [Period::make('2017-01-01', '2019-01-01'), Period::make('2018-01-01', '2018-02-01')],

            /*
             * A    [====]
             * B    [====]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-01', '2018-02-01')],
        ];
    }

    public function noOverlappingDates()
    {
        return [
            /*
             * A    [===]
             * B          [===]
             */
            [Period::make('2018-01-01', '2018-01-31'), Period::make('2018-02-01', '2018-02-28')],

            /*
             * A          [===]
             * B    [===]
             */
            [Period::make('2018-02-01', '2018-02-28'), Period::make('2018-01-01', '2018-01-31')],
        ];
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

        $this->assertTrue($a->overlapSingle($b)->equals($overlapPeriod));
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

        $overlapPeriods = $d->overlap($a, $b, $c);

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

        $overlap = $a->overlapAll($b, $c);

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

        $overlap = $a->overlapAll($b, $c);

        $this->assertNull($overlap);
    }

    /** @test */
    public function non_overlapping_dates_return_an_empty_collection()
    {
        $a = Period::make('2019-01-01', '2019-01-31');
        $b = Period::make('2019-02-01', '2019-02-28');

        $this->assertTrue($a->overlap($b)->isEmpty());
    }

    /** @test */
    public function it_can_determine_that_two_periods_do_not_overlap()
    {
        $a = Period::make('2018-01-05', '2018-01-10');
        $b = Period::make('2018-01-22', '2018-01-30');

        $overlap = $a->overlapSingle($b);

        $this->assertNull($overlap);
    }

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
    public function if_will_determine_that_there_is_no_gap_if_the_periods_only_touch_but_do_not_overlap()
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
    public function if_will_determine_that_there_is_no_gap_when_periods_overlap()
    {
        $a = Period::make('2018-01-15', '2018-01-31');

        $b = Period::make('2018-01-28', '2018-02-01');

        $gap = $a->gap($b);

        $this->assertNull($gap);
    }

    /**
     * @test
     *
     * A        [===========]
     * B            [===========]
     *
     * DIFF     [==]         [==]
     */
    public function if_can_create_a_diff_for_two_periods()
    {
        $a = Period::make('2018-01-01', '2018-01-15');

        $b = Period::make('2018-01-10', '2018-01-30');

        $diffs = $a->diffSingle($b);

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
    public function if_can_still_create_a_diff_for_two_periods_even_if_there_are_not_ordered()
    {
        $a = Period::make('2018-01-10', '2018-01-30');

        $b = Period::make('2018-01-01', '2018-01-15');

        $diffs = $a->diffSingle($b);

        $this->assertTrue($diffs[0]->equals(Period::make('2018-01-01', '2018-01-09')));
        $this->assertTrue($diffs[1]->equals(Period::make('2018-01-16', '2018-01-30')));
    }

    /**
     * @test
     *
     * A                    [=====]
     * B        [=====]
     *
     * DIFF     [=====]     [=====]
     */
    public function it_can_determine_the_diff_if_periods_do_not_overlap_at_all()
    {
        $a = Period::make('2018-01-10', '2018-01-15');

        $b = Period::make('2018-02-10', '2018-02-15');

        $diffs = $a->diffSingle($b);

        $this->assertTrue($diffs[0]->equals(Period::make('2018-01-10', '2018-01-15')));
        $this->assertTrue($diffs[1]->equals(Period::make('2018-02-10', '2018-02-15')));
    }

    /**
     * @test
     *
     * A       [=========]
     * B                     [==]
     * C                      [=========]
     * CURRENT         [===========]
     *
     * OVERLAP         [=]    [====]
     * DIFF               [=]
     */
    public function it_can_determine_the_diff_for_periods_with_multiple_overlaps()
    {
        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-02-10', '2018-02-20');
        $c = Period::make('2018-02-11', '2018-03-31');

        $current = Period::make('2018-01-20', '2018-03-15');

        $diff = $current->diff($a, $b, $c);

        $this->assertCount(1, $diff);

        $this->assertTrue($diff[0]->equals(Period::make('2018-02-01', '2018-02-09')));
    }

    /**
     * @test
     *
     * A                       [========]
     * B             [=========]
     * CURRENT         [============]
     *
     * OVERLAP         [============]
     * DIFF
     */
    public function if_all_periods_overlap_it_will_determine_that_there_is_no_diff()
    {
        $a = Period::make('2018-01-15', '2018-02-10');
        $b = Period::make('2017-12-20', '2018-01-15');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a, $b);

        $this->assertCount(0, $diff);
    }

    /**
     * @test
     *
     * A                            [========]
     * CURRENT         [=======]
     *
     * DIFF                     [==]
     */
    public function it_can_determine_that_there_is_a_diff()
    {
        $a = Period::make('2018-02-15', '2018-02-20');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a);

        $this->assertCount(1, $diff);

        $this->assertTrue($diff[0]->equals(Period::make('2018-02-01', '2018-02-14')));
    }

    /**
     * @test
     *
     * A                   [====]
     * B                               [========]
     * C         [=====]
     * CURRENT      [========================]
     *
     * DIFF             [=]      [====]
     */
    public function if_can_determine_multiple_diffs()
    {
        $a = Period::make('2018-01-05', '2018-01-10');
        $b = Period::make('2018-01-15', '2018-03-01');
        $c = Period::make('2017-01-01', '2018-01-02');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a, $b, $c);

        $this->assertCount(2, $diff);

        $this->assertTrue($diff[0]->equals(Period::make('2018-01-03', '2018-01-04')));
        $this->assertTrue($diff[1]->equals(Period::make('2018-01-11', '2018-01-14')));
    }

    /**
     * @test
     *
     * A                            [====]
     * B                [====]
     * CURRENT  [=============================]
     *
     * DIFF     [======]      [====]      [===]
     */
    public function if_can_determine_multiple_diffs_for_sure()
    {
        $a = Period::make('2018-01-15', '2018-01-20');
        $b = Period::make('2018-01-05', '2018-01-10');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a, $b);

        $this->assertCount(3, $diff);

        $this->assertTrue($diff[0]->equals(Period::make('2018-01-01', '2018-01-04')));
        $this->assertTrue($diff[1]->equals(Period::make('2018-01-11', '2018-01-14')));
        $this->assertTrue($diff[2]->equals(Period::make('2018-01-21', '2018-01-31')));
    }

    /** @test */
    public function it_accepts_carbon_instances()
    {
        $a = Period::make(Carbon::make('2018-01-01'), Carbon::make('2018-01-02'));

        $this->assertTrue($a->equals(Period::make('2018-01-01', '2018-01-02')));
    }

    /** @test */
    public function it_will_preserve_the_time()
    {
        $period = Period::make('2018-01-01 01:02:03', '2018-01-02 04:05:06');

        $this->assertTrue($period->equals(
            new Period(
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 01:02:03'),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 04:05:06')
            )
        ));
    }

    /** @test */
    public function if_will_use_the_start_of_day_when_passing_strings_to_a_period()
    {
        $period = Period::make('2018-01-01', '2018-01-02');

        $this->assertTrue($period->equals(
            new Period(
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00'),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')
            )
        ));
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

    /**
     * @test
     * @dataProvider expectedPeriodLengths
     */
    public function it_is_iterable(int $expectedCount, Period $period)
    {
        $this->assertSame($expectedCount, iterator_count($period));
    }

    /** @test */
    public function its_iterator_returns_immutable_dates()
    {
        $period = Period::make('2018-01-01', '2018-01-15');

        $this->assertInstanceOf(DateTimeImmutable::class, current($period));
    }

    /** @test */
    public function diff_filters_out_null_object_if_no_gap()
    {
        $a = Period::make('2019-02-01', '2019-02-01');

        $b = Period::make('2019-02-02', '2019-02-02');

        $diff = $a->diff($b);

        $this->assertEmpty($diff);
    }

    public function expectedPeriodLengths()
    {
        return [
            [1, Period::make('2018-01-01', '2018-01-01')],

            [15, Period::make('2018-01-01', '2018-01-15')],
            [14, Period::make('2018-01-01', '2018-01-15', null, Boundaries::EXCLUDE_START)],
            [14, Period::make('2018-01-01', '2018-01-15', null, Boundaries::EXCLUDE_END)],
            [13, Period::make('2018-01-01', '2018-01-15', null, Boundaries::EXCLUDE_ALL)],

            [24, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:59', Precision::HOUR)],
            [24, Period::make('2018-01-01 00:00:00', '2018-01-02 00:00:00', Precision::HOUR, Boundaries::EXCLUDE_END)],
        ];
    }

    /**
     * @test
     * @dataProvider periodsWithChangedPrecisions
     */
    public function its_precision_can_be_changed(int $expectedLength, int $targetPrecision, Period $period)
    {
        $this->assertSame($expectedLength, $period->withChangedPrecision($targetPrecision)->length());
    }

    public function periodsWithChangedPrecisions()
    {
        return [
            '2 days in hours' => [
                48,
                Precision::HOUR,
                Period::make('2019-04-01', '2019-04-02', Precision::DAY, Boundaries::EXCLUDE_NONE),
            ],
            '1 day in hours' => [
                24,
                Precision::HOUR,
                Period::make('2019-04-01', '2019-04-02', Precision::DAY, Boundaries::EXCLUDE_END),
            ],
            '1 week in hours' => [
                168,
                Precision::HOUR,
                Period::make('2019-04-01', '2019-04-07', Precision::DAY, Boundaries::EXCLUDE_NONE),
            ],
            '30 days in april' => [
                30,
                Precision::DAY,
                Period::make('2019-04-01', '2019-04-30', Precision::MONTH, Boundaries::EXCLUDE_NONE),
            ],
            '60 minutes in an hour' => [
                60,
                Precision::MINUTE,
                Period::make('2019-04-01 00:00:00', '2019-04-01 01:00:00', Precision::HOUR, Boundaries::EXCLUDE_END),
            ],
            '60 seconds in a minute' => [
                60,
                Precision::SECOND,
                Period::make('2019-04-01 00:00:00', '2019-04-01 00:01:00', Precision::MINUTE, Boundaries::EXCLUDE_END),
            ],
            '60 minutes are 1 hour' => [
                1,
                Precision::HOUR,
                Period::make('2019-04-01 00:00:00', '2019-04-01 01:00:00', Precision::HOUR, Boundaries::EXCLUDE_END),
            ],
            'any amount of days in one month are 1 month' => [
                1,
                Precision::MONTH,
                Period::make('2019-04-01', '2019-04-30', Precision::DAY, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of days spanning two months are 2 months' => [
                2,
                Precision::MONTH,
                Period::make('2019-04-01', '2019-05-31', Precision::DAY, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of days in a year is a year' => [
                1,
                Precision::YEAR,
                Period::make('2019-04-01', '2019-05-31', Precision::DAY, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of months in a year is a year' => [
                1,
                Precision::YEAR,
                Period::make('2019-04-01', '2019-05-31', Precision::MONTH, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of hours in a year is a year' => [
                1,
                Precision::YEAR,
                Period::make('2019-04-01', '2019-05-31', Precision::HOUR, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of hours spanning two years are two years' => [
                2,
                Precision::YEAR,
                Period::make('2019-04-01', '2020-05-31', Precision::HOUR, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of days spanning two years are two years' => [
                2,
                Precision::YEAR,
                Period::make('2019-04-01', '2020-05-31', Precision::DAY, Boundaries::EXCLUDE_NONE),
            ],
            'any amount of months spanning two years are two years' => [
                2,
                Precision::YEAR,
                Period::make('2019-04-01', '2020-05-31', Precision::MONTH, Boundaries::EXCLUDE_NONE),
            ],
        ];
    }
}

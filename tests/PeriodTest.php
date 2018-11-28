<?php

namespace Spatie\Tests\Period;

use Carbon\Carbon;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;

class PeriodTest extends TestCase
{
    /** @test */
    public function test_period_length()
    {
        $period = Period::make('2018-01-01', '2018-01-15');

        $this->assertEquals(15, $period->length());
    }

    /**
     * @test
     * @dataProvider overlappingDates
     */
    public function overlaps_with(Period $a, Period $b)
    {
        $this->assertTrue($a->overlapsWith($b));
    }

    /** @test */
    public function touches()
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
    public function does_not_overlap(Period $a, Period $b)
    {
        $this->assertFalse($a->overlapsWith($b));
    }

    public function overlappingDates(): array
    {
        return [
            /**
             * A    [=====]
             * B       [=====]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-15', '2018-02-15')],

            /**
             * A        [=====]
             * B    [=============]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-01-01', '2019-01-01')],

            /**
             * A        [=====]
             * B     [=====]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2017-12-01', '2018-01-15')],

            /**
             * A    [=============]
             * B        [=====]
             */
            [Period::make('2017-01-01', '2019-01-01'), Period::make('2018-01-01', '2018-02-01')],

            /**
             * A    [====]
             * B    [====]
             */
            [Period::make('2018-01-01', '2018-02-01'), Period::make('2018-01-01', '2018-02-01')],
        ];
    }

    public function noOverlappingDates()
    {
        return [
            /**
             * A    [===]
             * B          [===]
             */
            [Period::make('2018-01-01', '2018-01-31'), Period::make('2018-02-01', '2018-02-28')],

            /**
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
    public function test_overlapping_period()
    {
        $a = Period::make('2018-01-01', '2018-01-15');

        $b = Period::make('2018-01-10', '2018-01-30');

        $overlap = Period::make('2018-01-10', '2018-01-15');

        $this->assertTrue($a->overlapSingle($b)->equals($overlap));
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
    public function test_overlapping_multiple()
    {
        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-02-10', '2018-02-20');
        $c = Period::make('2018-03-01', '2018-03-31');
        $d = Period::make('2018-01-20', '2018-03-10');

        $overlap = $d->overlap($a, $b, $c);

        $this->assertCount(3, $overlap);

        [$first, $second, $third] = $overlap;

        $this->assertTrue($first->equals(Period::make('2018-01-20', '2018-01-31')));
        $this->assertTrue($second->equals(Period::make('2018-02-10', '2018-02-20')));
        $this->assertTrue($third->equals(Period::make('2018-03-01', '2018-03-10')));
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
    public function test_overlap_all()
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
     */
    public function test_no_overlap_reverse()
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
    public function test_gap()
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
    public function test_gap_reverse()
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
    public function test_gap_is_null_when_touching()
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
     * B    [=====]
     *
     * GAP
     */
    public function test_gap_is_null_when_overlap()
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
    public function diff_single()
    {
        $a = Period::make('2018-01-01', '2018-01-15');

        $b = Period::make('2018-01-10', '2018-01-30');

        [$first, $second] = $a->diffSingle($b);

        $this->assertTrue($first->equals(Period::make('2018-01-01', '2018-01-09')));
        $this->assertTrue($second->equals(Period::make('2018-01-16', '2018-01-30')));
    }

    /**
     * @test
     *
     * A             [==========]
     * B        [============]
     *
     * DIFF     [==]          [==]
     */
    public function diff_single_reverse()
    {
        $a = Period::make('2018-01-10', '2018-01-30');

        $b = Period::make('2018-01-01', '2018-01-15');

        [$first, $second] = $a->diffSingle($b);

        $this->assertTrue($first->equals(Period::make('2018-01-01', '2018-01-09')));
        $this->assertTrue($second->equals(Period::make('2018-01-16', '2018-01-30')));
    }

    /**
     * @test
     *
     * A                    [=====]
     * B        [=====]
     *
     * DIFF     [=====]     [=====]
     */
    public function diff_no_overlap()
    {
        $a = Period::make('2018-01-10', '2018-01-15');

        $b = Period::make('2018-02-10', '2018-02-15');

        [$first, $second] = $a->diffSingle($b);

        $this->assertTrue($first->equals(Period::make('2018-01-10', '2018-01-15')));
        $this->assertTrue($second->equals(Period::make('2018-02-10', '2018-02-15')));
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
    public function test_diff_multiple_with_double_overlaps()
    {
        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-02-10', '2018-02-20');
        $c = Period::make('2018-02-11', '2018-03-31');

        $current = Period::make('2018-01-20', '2018-03-15');

        $diff = $current->diff($a, $b, $c);

        $this->assertCount(1, $diff);

        [$first] = $diff;

        $this->assertTrue($first->equals(Period::make('2018-02-01', '2018-02-09')));
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
    public function test_empty_diff()
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
    public function test_diff_single()
    {
        $a = Period::make('2018-02-15', '2018-02-20');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a);

        $this->assertCount(1, $diff);

        [$first] = $diff;

        $this->assertTrue($first->equals(Period::make('2018-02-01', '2018-02-14')));
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
    public function diff_case_1()
    {
        $a = Period::make('2018-01-05', '2018-01-10');
        $b = Period::make('2018-01-15', '2018-03-01');
        $c = Period::make('2017-01-01', '2018-01-02');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a, $b, $c);

        $this->assertCount(2, $diff);

        [$first, $second] = $diff;

        $this->assertTrue($first->equals(Period::make('2018-01-03', '2018-01-04')));
        $this->assertTrue($second->equals(Period::make('2018-01-11', '2018-01-14')));
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
    public function diff_case_2()
    {
        $a = Period::make('2018-01-15', '2018-01-20');
        $b = Period::make('2018-01-05', '2018-01-10');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->diff($a, $b);

        $this->assertCount(3, $diff);

        [$first, $second, $third] = $diff;

        $this->assertTrue($first->equals(Period::make('2018-01-01', '2018-01-04')));
        $this->assertTrue($second->equals(Period::make('2018-01-11', '2018-01-14')));
        $this->assertTrue($third->equals(Period::make('2018-01-21', '2018-01-31')));
    }

    /** @test */
    public function test_with_carbon()
    {
        $a = Period::make(Carbon::make('2018-01-01'), Carbon::make('2018-01-02'));

        $this->assertTrue($a->equals(Period::make('2018-01-01', '2018-01-02')));
    }

    /** @test */
    public function make_with_time_keeps_the_time()
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
    public function make_without_time_set_time_to_start_of_day()
    {
        $period = Period::make('2018-01-01', '2018-01-02');

        $this->assertTrue($period->equals(
            new Period(
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00'),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')
            )
        ));
    }
}

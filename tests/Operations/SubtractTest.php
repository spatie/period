<?php

namespace Spatie\Period\Tests\Operations;

use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

class SubtractTest extends TestCase
{
    /**
     * @test
     *
     * A      [=====]
     * B                [=====]
     *
     * RES    [=====]
     */
    public function subtraction_without_overlap()
    {
        $a = Period::make('2020-01-01', '2020-01-02');
        $b = Period::make('2020-02-01', '2020-02-02');

        $result = $a->subtract($b);

        $this->assertCount(1, $result);

        $this->assertTrue($result[0]->equals($a));
    }

    /**
     * @test
     *
     * A        [=======]
     * B            [=======]
     *
     * RES     [==]
     */
    public function subtraction_right()
    {
        $a = Period::make('2020-01-01', '2020-01-31');
        $b = Period::make('2020-01-11', '2020-01-31');

        $result = $a->subtract($b);

        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->equals(Period::make('2020-01-01', '2020-01-10')));
    }

    /**
     * @test
     *
     * A            [=======]
     * B        [=======]
     *
     * RES               [==]
     */
    public function subtraction_left()
    {
        $a = Period::make('2020-01-01', '2020-01-31');
        $b = Period::make('2020-01-01', '2020-01-09');

        $result = $a->subtract($b);

        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->equals(Period::make('2020-01-10', '2020-01-31')));
    }

    /**
     * @test
     *
     * A        [=================]
     * B             [=======]
     *
     * RES      [==]          [==]
     */
    public function subtraction_left_and_right()
    {
        $a = Period::make('2020-01-01', '2020-01-31');
        $b = Period::make('2020-01-11', '2020-01-14');

        $result = $a->subtract($b);

        $this->assertCount(2, $result);
        $this->assertTrue($result[0]->equals(Period::make('2020-01-01', '2020-01-10')));
        $this->assertTrue($result[1]->equals(Period::make('2020-01-15', '2020-01-31')));
    }

    /**
     * @test
     *
     * A             [=======]
     * B        [=================]
     *
     * RES      [==]          [==]
     */
    public function subtraction_full()
    {
        $a = Period::make('2020-01-11', '2020-01-14');
        $b = Period::make('2020-01-01', '2020-01-31');

        $result = $a->subtract($b);

        $this->assertCount(0, $result);
    }

    /**
     * @test
     *
     * CURRENT         [===========]
     *
     * A       [=========]
     * B                     [==]
     * C                      [=========]
     *
     * RESULT             [=]
     */
    public function subtraction_many()
    {
        $current = Period::make('2018-01-20', '2018-03-15');

        $a = Period::make('2018-01-01', '2018-01-31');
        $b = Period::make('2018-02-10', '2018-02-20');
        $c = Period::make('2018-02-11', '2018-03-31');

        $diff = $current->subtract($a, $b, $c);

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

        $diff = $current->subtract($a, $b);

        $this->assertCount(0, $diff);
    }

    /**
     * @test
     *
     * CURRENT         [=======]
     *
     * A                            [========]
     *
     * DIFF             [=======]
     */
    public function it_can_subtract()
    {
        $a = Period::make('2018-02-15', '2018-02-20');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->subtract($a);

        $this->assertCount(1, $diff);
        $this->assertTrue($diff[0]->equals($current));
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
    public function it_can_determine_multiple_diffs()
    {
        $a = Period::make('2018-01-05', '2018-01-10');
        $b = Period::make('2018-01-15', '2018-03-01');
        $c = Period::make('2017-01-01', '2018-01-02');

        $current = Period::make('2018-01-01', '2018-01-31');

        $diff = $current->subtract($a, $b, $c);

        $this->assertCount(2, $diff);

        $this->assertTrue($diff[0]->equals(Period::make('2018-01-03', '2018-01-04')));
        $this->assertTrue($diff[1]->equals(Period::make('2018-01-11', '2018-01-14')));
    }

    /**
     * @test
     *
     * CURRENT  [=============================]
     *
     * A                            [====]
     * B                [====]
     *
     * DIFF     [======]      [====]      [===]
     */
    public function it_can_determine_multiple_diffs_for_sure()
    {
        $current = Period::make('2018-01-01', '2018-01-31');

        $a = Period::make('2018-01-15', '2018-01-20');
        $b = Period::make('2018-01-05', '2018-01-10');

        $diff = $current->subtract($a, $b);

        $this->assertCount(3, $diff);

        $this->assertTrue($diff[0]->equals(Period::make('2018-01-01', '2018-01-04')));
        $this->assertTrue($diff[1]->equals(Period::make('2018-01-11', '2018-01-14')));
        $this->assertTrue($diff[2]->equals(Period::make('2018-01-21', '2018-01-31')));
    }

    /** @test */
    public function passing_empty_period_collection_returns_same_period_within_collection()
    {
        $current = Period::make('2018-01-01', '2018-01-31');
        $emptyCollection = new PeriodCollection;

        $diff = $current->subtract(...$emptyCollection);

        $this->assertInstanceOf(PeriodCollection::class, $diff);
        $this->assertCount(1, $diff);
        $this->assertTrue($diff[0]->equals($current));
    }
}

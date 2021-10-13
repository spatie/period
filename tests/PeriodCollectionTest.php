<?php

namespace Spatie\Period\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

class PeriodCollectionTest extends TestCase
{
    /**
     * @test
     *
     * A            [=====]      [===========]
     * B          [=========]          [========]
     *
     * OVERLAP      [=====]            [=====]
     */
    public function it_can_determine_multiple_overlaps_for_a_single_collection()
    {
        $a = new PeriodCollection(
            Period::make('2018-01-05', '2018-01-10'),
            Period::make('2018-01-20', '2018-01-25')
        );

        $b = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-15'),
            Period::make('2018-01-22', '2018-01-30')
        );

        $overlapPeriods = $a->overlapAll($b);

        $this->assertCount(2, $overlapPeriods);

        $this->assertTrue($overlapPeriods[0]->equals(Period::make('2018-01-05', '2018-01-10')));
        $this->assertTrue($overlapPeriods[1]->equals(Period::make('2018-01-22', '2018-01-25')));
    }

    /**
     * @test
     *
     *
     * A            [=====]      [===========]
     * B            [=================]
     * C                [====================]
     *
     * OVERLAP          [=]      [====]
     */
    public function it_can_determine_multiple_overlaps_for_multiple_collections()
    {
        $a = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-07'),
            Period::make('2018-01-15', '2018-01-25')
        );

        $b = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-20')
        );

        $c = new PeriodCollection(
            Period::make('2018-01-06', '2018-01-25')
        );

        $overlapPeriods = $a->overlapAll($b, $c);

        $this->assertCount(2, $overlapPeriods);

        $this->assertTrue($overlapPeriods[0]->equals(Period::make('2018-01-06', '2018-01-07')));
        $this->assertTrue($overlapPeriods[1]->equals(Period::make('2018-01-15', '2018-01-20')));
    }

    /**
     * @test
     *
     * A                   [====]
     * B                               [========]
     * C           [=====]
     * D                                             [====]
     *
     * BOUNDARIES  [=======================================]
     */
    public function it_can_determine_the_boundaries_of_a_collection()
    {
        $collection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-05'),
            Period::make('2018-01-10', '2018-01-15'),
            Period::make('2018-01-20', '2018-01-25'),
            Period::make('2018-01-30', '2018-01-31')
        );

        $boundaries = $collection->boundaries();

        $this->assertTrue($boundaries->equals(Period::make('2018-01-01', '2018-01-31')));
    }

    /**
     * @test
     *
     * A                   [====]
     * B                               [========]
     * C         [=====]
     * D                                             [====]
     *
     * GAPS             [=]      [====]          [==]
     */
    public function it_can_determine_the_gaps_of_a_collection()
    {
        $collection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-05'),
            Period::make('2018-01-10', '2018-01-15'),
            Period::make('2018-01-20', '2018-01-25'),
            Period::make('2018-01-30', '2018-01-31')
        );

        $gaps = $collection->gaps();

        $this->assertCount(3, $gaps);

        $this->assertTrue($gaps[0]->equals(Period::make('2018-01-06', '2018-01-09')));
        $this->assertTrue($gaps[1]->equals(Period::make('2018-01-16', '2018-01-19')));
        $this->assertTrue($gaps[2]->equals(Period::make('2018-01-26', '2018-01-29')));
    }

    /**
     * @test
     *
     * A         [=====)
     * B         [=====)
     * C         [=====)
     *
     * GAP
     */
    public function no_gaps_when_periods_fully_overlap_and_end_excluded()
    {
        $collection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END()),
            Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END()),
            Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END())
        );

        $gaps = $collection->gaps();

        $this->assertCount(0, $gaps);
    }

    /**
     * @test
     *
     * A        [========================]
     * B    [================]
     * C                    [=====================]
     * D                                    [=====]
     *
     * LIMIT        [===============]
     *
     * A            [===============]
     * B            [========]
     * C                    [=======]
     */
    public function intersect_test()
    {
        $collection = new PeriodCollection(
            Period::make('2019-01-05', '2019-01-15'),
            Period::make('2019-01-01', '2019-01-10'),
            Period::make('2019-01-10', '2019-01-15'),
            Period::make('2019-02-01', '2019-02-15')
        );

        $intersect = $collection->intersect(Period::make('2019-01-09', '2019-01-11'));

        $this->assertCount(3, $intersect);

        $this->assertTrue($intersect[0]->equals(Period::make('2019-01-09', '2019-01-11')));
        $this->assertTrue($intersect[1]->equals(Period::make('2019-01-09', '2019-01-10')));
        $this->assertTrue($intersect[2]->equals(Period::make('2019-01-10', '2019-01-11')));
    }

    /** @test */
    public function map()
    {
        $collection = new PeriodCollection(
            Period::make('2019-01-01', '2019-01-02'),
            Period::make('2019-01-01', '2019-01-02')
        );

        $mapped = $collection->map(function (Period $period) {
            return $period;
        });

        $this->assertTrue($mapped[0]->equals($collection[0]));
        $this->assertTrue($mapped[1]->equals($collection[1]));
    }

    /** @test */
    public function reduce()
    {
        $collection = new PeriodCollection(
            Period::make('2019-01-01', '2019-01-02'),
            Period::make('2019-01-03', '2019-01-04')
        );

        $totalLength = $collection->reduce(function (int $carry, Period $period) {
            return $carry + $period->length();
        }, 0);

        $this->assertEquals(4, $totalLength);
    }

    /**
     * @test
     *
     * A           [=======] [===============]
     *
     * SUBTRACT                      [=]
     *
     * RESULT      [=======] [======]   [====]
     */
    public function subtract_a_period_from_period_collection()
    {
        $a = new PeriodCollection(
            Period::make('1987-02-01', '1987-02-10'),
            Period::make('1987-02-11', '1987-02-28')
        );

        $subtract = Period::make('1987-02-20', '1987-02-21');

        $result = $a->subtract($subtract);

        $this->assertTrue(Period::make('1987-02-01', '1987-02-10')->equals($result[0]));
        $this->assertTrue(Period::make('1987-02-11', '1987-02-19')->equals($result[1]));
        $this->assertTrue(Period::make('1987-02-22', '1987-02-28')->equals($result[2]));
    }

    /**
     * @test
     *
     * A           [=======] [===============]
     *
     * SUBTRACT       [=]            [=]
     *
     * RESULT      [=]   [=] [======]   [====]
     */
    public function subtract_a_period_collection_from_period_collection()
    {
        $a = new PeriodCollection(
            Period::make('1987-02-01', '1987-02-10'),
            Period::make('1987-02-11', '1987-02-28')
        );

        $subtract = new PeriodCollection(
            Period::make('1987-02-05', '1987-02-06'),
            Period::make('1987-02-20', '1987-02-21'),
        );

        $result = $a->subtract($subtract);

        $this->assertTrue(Period::make('1987-02-01', '1987-02-04')->equals($result[0]));
        $this->assertTrue(Period::make('1987-02-07', '1987-02-10')->equals($result[1]));
        $this->assertTrue(Period::make('1987-02-11', '1987-02-19')->equals($result[2]));
        $this->assertTrue(Period::make('1987-02-22', '1987-02-28')->equals($result[3]));
    }

    /** @test */
    public function filter()
    {
        $collection = new PeriodCollection(
            Period::make('2019-01-01', '2019-01-02'),
            Period::make('2019-02-01', '2019-02-02')
        );

        $filtered = $collection->filter(function (Period $period) {
            return $period->startsAt(new DateTime('2019-01-01'));
        });

        $this->assertCount(1, $filtered);
        $this->assertTrue($filtered[0]->equals($collection[0]));
    }

    /** @test */
    public function it_loops_after_filter()
    {
        $collection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-10', '2018-01-15'),
            Period::make('2018-01-20', '2018-01-25'),
            Period::make('2018-01-30', '2018-01-31')
        );

        $filtered = $collection->filter(function (Period $period) {
            return $period->length() > 2;
        });

        $items = [];
        foreach ($filtered as $item) {
            $items[] = $item;
        }
        $this->assertEquals($filtered->count(), count($items));
    }

    /** @test */
    public function it_substracts_empty_period_collection()
    {
        $collection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-10', '2018-01-15'),
            Period::make('2018-01-20', '2018-01-25'),
            Period::make('2018-01-30', '2018-01-31')
        );

        $emptyCollection = new PeriodCollection();

        $collection->subtract($emptyCollection);

        $this->assertCount(4, $collection);
    }

    /** @test */
    public function it_filters_duplicate_periods_in_collection()
    {
        $collection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-30', '2018-01-31')
        );

        $unique = $collection->unique();

        $this->assertCount(6, $collection);
        $this->assertCount(2, $unique);
        $this->assertTrue($unique[0]->equals($collection[0]));
        $this->assertTrue($unique[1]->equals($collection[5]));
    }
}

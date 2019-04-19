<?php

namespace Spatie\Period\Tests;

use DateTimeImmutable;
use Spatie\Period\Period;
use PHPUnit\Framework\TestCase;
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

        $overlapPeriods = $a->overlap($b);

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

        $overlapPeriods = $a->overlap($b, $c);

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
     * A    [===============]
     * B        |
     * C  |
     * D                        |
     */
    public function it_can_determine_whether_a_period_has_a_date()
    {
        $period = Period::make('2018-01-01', '2018-01-31');

        $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-01')));
        $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-31')));
        $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-10')));

        $this->assertFalse($period->contains(new DateTimeImmutable('2017-12-31')));
        $this->assertFalse($period->contains(new DateTimeImmutable('2018-02-01')));
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
}

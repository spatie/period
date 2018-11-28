<?php

namespace Spatie\Tests\Period;

use PHPUnit\Framework\TestCase;
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

        $overlapPeriods = $a->overlapSingle($b);

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
}

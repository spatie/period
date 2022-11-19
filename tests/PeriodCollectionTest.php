<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

/**
 * A            [=====]      [===========]
 * B          [=========]          [========]
 *
 * OVERLAP      [=====]            [=====]
 */
it('can determine multiple overlaps for a single collection', function () {
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
});

/**
 *
 * A            [=====]      [===========]
 * B            [=================]
 * C                [====================]
 *
 * OVERLAP          [=]      [====]
 */
it('can determine multiple overlaps for multiple collections', function () {
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
});

/**
 * A                   [====]
 * B                               [========]
 * C           [=====]
 * D                                             [====]
 *
 * BOUNDARIES  [=======================================]
 */
it('can determine the boundaries of a collection', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-05'),
        Period::make('2018-01-10', '2018-01-15'),
        Period::make('2018-01-20', '2018-01-25'),
        Period::make('2018-01-30', '2018-01-31')
    );

    $boundaries = $collection->boundaries();

    $this->assertTrue($boundaries->equals(Period::make('2018-01-01', '2018-01-31')));
});

/**
 * A                   [====]
 * B                               [========]
 * C         [=====]
 * D                                             [====]
 *
 * GAPS             [=]      [====]          [==]
 */
it('can determine the gaps of a collection', function () {
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
});

/**
 * A         [=====)
 * B         [=====)
 * C         [=====)
 *
 * GAP
 */
it('no gaps when periods fully overlap and end excluded', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END()),
        Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END()),
        Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END())
    );

    $gaps = $collection->gaps();

    $this->assertCount(0, $gaps);
});

/**
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
it('intersect test', function () {
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
});

/**
 * A           [=======] [===============]
 *
 * SUBTRACT                      [=]
 *
 * RESULT      [=======] [======]   [====]
 */
it('subtract a period from period collection', function () {
    $a = new PeriodCollection(
        Period::make('1987-02-01', '1987-02-10'),
        Period::make('1987-02-11', '1987-02-28')
    );

    $subtract = Period::make('1987-02-20', '1987-02-21');

    $result = $a->subtract($subtract);

    $this->assertTrue(Period::make('1987-02-01', '1987-02-10')->equals($result[0]));
    $this->assertTrue(Period::make('1987-02-11', '1987-02-19')->equals($result[1]));
    $this->assertTrue(Period::make('1987-02-22', '1987-02-28')->equals($result[2]));
});

/**
 * A           [=======] [===============]
 *
 * SUBTRACT       [=]            [=]
 *
 * RESULT      [=]   [=] [======]   [====]
 */
it('subtract a period collection from period collection', function () {
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
});

it('can filter', function () {
    $collection = new PeriodCollection(
        Period::make('2019-01-01', '2019-01-02'),
        Period::make('2019-02-01', '2019-02-02')
    );

    $filtered = $collection->filter(function (Period $period) {
        return $period->startsAt(new DateTime('2019-01-01'));
    });

    $this->assertCount(1, $filtered);
    $this->assertTrue($filtered[0]->equals($collection[0]));
});

it('loops after filter', function () {
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
});

it('substracts empty period collection', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-02'),
        Period::make('2018-01-10', '2018-01-15'),
        Period::make('2018-01-20', '2018-01-25'),
        Period::make('2018-01-30', '2018-01-31')
    );

    $emptyCollection = new PeriodCollection();

    $collection->subtract($emptyCollection);

    $this->assertCount(4, $collection);
});

it('filters duplicate periods in collection', function () {
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
});

it('sorts collection', function () {
    $periods = [
        3 => Period::make('2018-01-30', '2018-01-31'),
        1 => Period::make('2018-01-10', '2018-01-15'),
        2 => Period::make('2018-01-20', '2018-01-25'),
        0 => Period::make('2018-01-01', '2018-01-02'),
    ];

    $collection = new PeriodCollection(...$periods);

    $sorted = $collection->sort();

    $this->assertTrue($sorted[0]->equals($periods[0]));
    $this->assertTrue($sorted[1]->equals($periods[1]));
    $this->assertTrue($sorted[2]->equals($periods[2]));
    $this->assertTrue($sorted[3]->equals($periods[3]));
});

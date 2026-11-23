<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Exceptions\CannotComparePeriods;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

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

    expect($overlapPeriods)->toHaveCount(2);

    expect($overlapPeriods[0]->equals(Period::make('2018-01-05', '2018-01-10')))->toBeTrue();
    expect($overlapPeriods[1]->equals(Period::make('2018-01-22', '2018-01-25')))->toBeTrue();
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

    expect($overlapPeriods)->toHaveCount(2);

    expect($overlapPeriods[0]->equals(Period::make('2018-01-06', '2018-01-07')))->toBeTrue();
    expect($overlapPeriods[1]->equals(Period::make('2018-01-15', '2018-01-20')))->toBeTrue();
});

/**
 * A            [==]               [===]
 * B                              [=]
 * B           [=============================]
 * B             []
 *
 * OVERLAP      [==]               []
 * OVERLAP       []                [===]
 */
it('determines overlaps with unsorted periods in the order of both collections', function () {
    $a = new PeriodCollection(
        Period::make('2018-01-02', '2018-01-05'),
        Period::make('2018-01-21', '2018-01-25')
    );

    $b = new PeriodCollection(
        Period::make('2018-01-20', '2018-01-22'),
        Period::make('2018-01-01', '2018-01-31'),
        Period::make('2018-01-03', '2018-01-04')
    );

    $overlapPeriods = $a->overlapAll($b);

    expect($overlapPeriods)->toHaveCount(4);

    expect($overlapPeriods[0]->equals(Period::make('2018-01-02', '2018-01-05')))->toBeTrue();
    expect($overlapPeriods[1]->equals(Period::make('2018-01-03', '2018-01-04')))->toBeTrue();
    expect($overlapPeriods[2]->equals(Period::make('2018-01-21', '2018-01-22')))->toBeTrue();
    expect($overlapPeriods[3]->equals(Period::make('2018-01-21', '2018-01-25')))->toBeTrue();
});

it('cannot determine overlaps with periods of another precision', function () {
    $a = new PeriodCollection(Period::make('2018-01-01', '2018-01-05'));
    $b = new PeriodCollection(Period::make('2019-01-01', '2019-03-01', Precision::MONTH()));

    expect(fn () => $a->overlapAll($b))->toThrow(CannotComparePeriods::class);
});

it('determines no overlaps with an empty collection', function () {
    $a = new PeriodCollection(Period::make('2018-01-01', '2018-01-05'));
    $empty = new PeriodCollection();

    expect($a->overlapAll($empty))->toHaveCount(0);
    expect($empty->overlapAll($a))->toHaveCount(0);
});

it('keeps insertion order for equal starts when sorting keyed periods', function () {
    $first = Period::make('2018-01-01', '2018-01-20');
    $second = Period::make('2018-01-01', '2018-01-10');
    $collection = new PeriodCollection(z: $first, a: $second);

    expect(iterator_to_array($collection->sort()))->toBe([$first, $second]);
});

it('determines overlaps in insertion order with keyed periods', function () {
    $first = Period::make('2018-01-01', '2018-01-20');
    $second = Period::make('2018-01-01', '2018-01-10');
    $collection = new PeriodCollection(Period::make('2018-01-01', '2018-01-31'));

    $overlaps = $collection->overlapAll(new PeriodCollection(z: $first, a: $second));

    expect($overlaps)->toHaveCount(2);
    expect($overlaps[0]->equals($first))->toBeTrue();
    expect($overlaps[1]->equals($second))->toBeTrue();
});

it('determines boundaries gaps and union after the first period is removed', function () {
    $first = Period::make('2018-01-01', '2018-01-05');
    $second = Period::make('2018-01-10', '2018-01-15');
    $third = Period::make('2018-01-20', '2018-01-25');
    $collection = new PeriodCollection($first, $second, $third);

    unset($collection[0]);

    expect($collection->boundaries()->equals(Period::make('2018-01-10', '2018-01-25')))->toBeTrue();

    $gaps = $collection->gaps();

    expect($gaps)->toHaveCount(1);
    expect($gaps[0]->equals(Period::make('2018-01-16', '2018-01-19')))->toBeTrue();

    $union = $collection->union();

    expect($union)->toHaveCount(2);
    expect($union[0]->equals($second))->toBeTrue();
    expect($union[1]->equals($third))->toBeTrue();
});

it('reduces periods appended by the callback', function () {
    $period = Period::make('2018-01-01', '2018-01-05');
    $collection = new PeriodCollection($period, $period);

    $count = $collection->reduce(function (int $count) use ($collection, $period) {
        if ($count === 0) {
            $collection[] = $period;
        }

        return $count + 1;
    }, 0);

    expect($count)->toBe(3);
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

    expect($boundaries->equals(Period::make('2018-01-01', '2018-01-31')))->toBeTrue();
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

    expect($gaps)->toHaveCount(3);

    expect($gaps[0]->equals(Period::make('2018-01-06', '2018-01-09')))->toBeTrue();
    expect($gaps[1]->equals(Period::make('2018-01-16', '2018-01-19')))->toBeTrue();
    expect($gaps[2]->equals(Period::make('2018-01-26', '2018-01-29')))->toBeTrue();
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

    expect($gaps)->toHaveCount(0);
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

    expect($intersect)->toHaveCount(3);

    expect($intersect[0]->equals(Period::make('2019-01-09', '2019-01-11')))->toBeTrue();
    expect($intersect[1]->equals(Period::make('2019-01-09', '2019-01-10')))->toBeTrue();
    expect($intersect[2]->equals(Period::make('2019-01-10', '2019-01-11')))->toBeTrue();
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

    expect(Period::make('1987-02-01', '1987-02-10')->equals($result[0]))->toBeTrue();
    expect(Period::make('1987-02-11', '1987-02-19')->equals($result[1]))->toBeTrue();
    expect(Period::make('1987-02-22', '1987-02-28')->equals($result[2]))->toBeTrue();
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

    expect(Period::make('1987-02-01', '1987-02-04')->equals($result[0]))->toBeTrue();
    expect(Period::make('1987-02-07', '1987-02-10')->equals($result[1]))->toBeTrue();
    expect(Period::make('1987-02-11', '1987-02-19')->equals($result[2]))->toBeTrue();
    expect(Period::make('1987-02-22', '1987-02-28')->equals($result[3]))->toBeTrue();
});

it('can filter', function () {
    $collection = new PeriodCollection(
        Period::make('2019-01-01', '2019-01-02'),
        Period::make('2019-02-01', '2019-02-02')
    );

    $filtered = $collection->filter(function (Period $period) {
        return $period->startsAt(new DateTime('2019-01-01'));
    });

    expect($filtered)->toHaveCount(1);
    expect($filtered[0]->equals($collection[0]))->toBeTrue();
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
    expect(count($items))->toEqual($filtered->count());
});

it('loops over the periods after an unset one', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-05'),
        Period::make('2018-01-10', '2018-01-15'),
        Period::make('2018-01-20', '2018-01-25')
    );

    unset($collection[1]);

    expect(array_keys(iterator_to_array($collection)))->toEqual([0, 2]);
    expect($collection->boundaries()->equals(Period::make('2018-01-01', '2018-01-25')))->toBeTrue();

    $gaps = $collection->gaps();

    expect($gaps)->toHaveCount(1);
    expect($gaps[0]->equals(Period::make('2018-01-06', '2018-01-19')))->toBeTrue();
});

it('skips periods unset while looping', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-05'),
        Period::make('2018-01-10', '2018-01-15'),
        Period::make('2018-01-20', '2018-01-25')
    );

    $keys = [];

    foreach ($collection as $key => $period) {
        if ($key === 0) {
            unset($collection[1]);
        }

        $keys[] = $key;
    }

    expect($keys)->toEqual([0, 2]);
});

it('loops over periods appended while looping', function () {
    $period = Period::make('2018-01-01', '2018-01-05');
    $collection = new PeriodCollection($period, $period);

    unset($collection[0]);

    $keys = [];

    foreach ($collection as $key => $current) {
        if ($key === 1) {
            $collection[] = $period;
        }

        $keys[] = $key;
    }

    expect($keys)->toEqual([1, 2]);
});

it('is valid before a rewind when the first key was unset', function () {
    $period = Period::make('2018-01-01', '2018-01-05');
    $collection = new PeriodCollection($period, $period);

    unset($collection[0]);

    expect($collection->valid())->toBeTrue();
    expect($collection->key())->toBe(1);
    expect($collection->current())->toBe($period);
    expect($collection->boundaries()->equals($period))->toBeTrue();
    expect($collection->gaps())->toHaveCount(0);
    expect($collection->union())->toHaveCount(1);
});

it('visits remaining integer keys even when inserted out of order', function () {
    $period = Period::make('2018-01-01', '2018-01-05');
    $collection = new PeriodCollection($period);
    $collection[10] = $period;
    $collection[3] = $period;
    $collection['label'] = $period;

    expect(array_keys(iterator_to_array($collection)))->toBe([0, 3, 10]);
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

    expect($collection)->toHaveCount(4);
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

    expect($collection)->toHaveCount(6);
    expect($unique)->toHaveCount(2);
    expect($unique[0]->equals($collection[0]))->toBeTrue();
    expect($unique[1]->equals($collection[5]))->toBeTrue();
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

    expect($sorted[0]->equals($periods[0]))->toBeTrue();
    expect($sorted[1]->equals($periods[1]))->toBeTrue();
    expect($sorted[2]->equals($periods[2]))->toBeTrue();
    expect($sorted[3]->equals($periods[3]))->toBeTrue();
});

it('unions collection', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-30', '2018-01-31'),
        Period::make('2018-01-30', '2018-02-2'),
        Period::make('2018-02-04', '2018-02-05'),
        Period::make('2018-02-07', '2018-02-09'),
        Period::make('2018-02-12', '2018-02-14'),
        Period::make('2018-02-08', '2018-02-13')
    );

    $unioned = $collection->union();

    expect($unioned)->toHaveCount(3);
    expect($unioned[0]->start() == $collection[0]->start())->toBeTrue();
    expect($unioned[0]->end() == $collection[1]->end())->toBeTrue();
    expect($unioned[1]->start() == $collection[2]->start())->toBeTrue();
    expect($unioned[1]->end() == $collection[2]->end())->toBeTrue();
    expect($unioned[2]->start() == $collection[3]->start())->toBeTrue();
    expect($unioned[2]->end() == $collection[4]->end())->toBeTrue();
});

it('adds periods to a copy of the collection', function () {
    $collection = new PeriodCollection(Period::make('2018-01-01', '2018-01-05'));

    $added = $collection->add(Period::make('2018-01-10', '2018-01-15'), Period::make('2018-01-20', '2018-01-25'));

    expect($collection)->toHaveCount(1);
    expect($added)->toHaveCount(3);
    expect($added[2]->equals(Period::make('2018-01-20', '2018-01-25')))->toBeTrue();
});

it('maps periods', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-05'),
        Period::make('2018-01-10', '2018-01-15')
    );

    $renewed = $collection->map(fn (Period $period) => $period->renew());

    expect($renewed[0]->equals(Period::make('2018-01-06', '2018-01-10')))->toBeTrue();
    expect($renewed[1]->equals(Period::make('2018-01-16', '2018-01-21')))->toBeTrue();
    expect($collection[0]->equals(Period::make('2018-01-01', '2018-01-05')))->toBeTrue();
});

it('reduces periods', function () {
    $collection = new PeriodCollection(
        Period::make('2018-01-01', '2018-01-05'),
        Period::make('2018-01-10', '2018-01-15')
    );

    $length = $collection->reduce(fn (int $carry, Period $period) => $carry + $period->length(), 0);

    expect($length)->toEqual(11);
});

it('can be accessed as an array', function () {
    $collection = new PeriodCollection(Period::make('2018-01-01', '2018-01-05'));

    expect(isset($collection[0]))->toBeTrue();
    expect(isset($collection[1]))->toBeFalse();
    expect($collection[1])->toBeNull();

    $collection[0] = Period::make('2018-01-10', '2018-01-15');

    expect($collection)->toHaveCount(1);
    expect($collection[0]->equals(Period::make('2018-01-10', '2018-01-15')))->toBeTrue();
});

it('has no boundaries, gaps or union when empty', function () {
    $collection = new PeriodCollection();

    expect($collection->boundaries())->toBeNull();
    expect($collection->gaps())->toHaveCount(0);
    expect($collection->union())->toHaveCount(0);
});

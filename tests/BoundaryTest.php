<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Period;

it('exclude none', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_NONE());

    $this->assertFalse($period->isStartExcluded());
    $this->assertFalse($period->isEndExcluded());
});

it('exclude start', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_START());

    $this->assertTrue($period->isStartExcluded());
    $this->assertFalse($period->isEndExcluded());
});

it('exclude end', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_END());

    $this->assertFalse($period->isStartExcluded());
    $this->assertTrue($period->isEndExcluded());
});

it('exclude all', function () {
    $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_ALL());

    $this->assertTrue($period->isStartExcluded());
    $this->assertTrue($period->isEndExcluded());
});

it('length with boundaries', function ($expectedAmount, Period $period) {
    $this->assertEquals($expectedAmount, $period->length());
})->with('periods_with_amounts_of_included_dates');

it('overlap with excluded boundaries', function () {
    $a = Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END());
    $b = Period::make('2018-01-05', '2018-01-10');
    $this->assertFalse($a->overlapsWith($b));

    $a = Period::make('2018-01-01', '2018-01-05');
    $b = Period::make('2018-01-05', '2018-01-10', boundaries: Boundaries::EXCLUDE_START());
    $this->assertFalse($a->overlapsWith($b));

    $a = Period::make('2018-01-01', '2018-01-05');
    $b = Period::make('2018-01-05', '2018-01-10');
    $this->assertTrue($a->overlapsWith($b));
});

it('subtract with boundaries', function (string $period, string $subtract, string $result) {
    $this->assertEquals(
        $result,
        Period::fromString($period)
        ->subtract(Period::fromString($subtract))[0]
        ->asString()
    );
})->with('boundaries_for_subtract');

it('overlap with boundaries', function (string $period, string $overlap, string $result) {
    $this->assertEquals(
        $result,
        Period::fromString($period)
        ->overlap(Period::fromString($overlap))
        ->asString()
    );
})->with('boundaries_for_overlap');

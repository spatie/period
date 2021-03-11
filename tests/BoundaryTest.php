<?php

namespace Spatie\Period\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class BoundaryTest extends TestCase
{
    /** @test */
    public function exclude_none()
    {
        $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_NONE());

        $this->assertFalse($period->isStartExcluded());
        $this->assertFalse($period->isEndExcluded());
    }

    /** @test */
    public function exclude_start()
    {
        $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_START());

        $this->assertTrue($period->isStartExcluded());
        $this->assertFalse($period->isEndExcluded());
    }

    /** @test */
    public function exclude_end()
    {
        $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_END());

        $this->assertFalse($period->isStartExcluded());
        $this->assertTrue($period->isEndExcluded());
    }

    /** @test */
    public function exclude_all()
    {
        $period = Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_ALL());

        $this->assertTrue($period->isStartExcluded());
        $this->assertTrue($period->isEndExcluded());
    }

    /**
     * @test
     * @dataProvider periodsWithAmountsOfIncludedDates
     */
    public function length_with_boundaries($expectedAmount, Period $period)
    {
        $this->assertEquals($expectedAmount, $period->length());
    }

    public function periodsWithAmountsOfIncludedDates(): Generator
    {
        yield [4, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_NONE())];
        yield [3, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_START())];
        yield [3, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_END())];
        yield [2, Period::make('2016-01-01', '2019-02-05', Precision::YEAR(), Boundaries::EXCLUDE_ALL())];

        yield [43, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_NONE())];
        yield [42, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_START())];
        yield [42, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_END())];
        yield [41, Period::make('2016-01-01', '2019-07-05', Precision::MONTH(), Boundaries::EXCLUDE_ALL())];

        yield [31, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_NONE())];
        yield [30, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_START())];
        yield [30, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_END())];
        yield [29, Period::make('2018-01-01', '2018-01-31', boundaries: Boundaries::EXCLUDE_ALL())];

        yield [24, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_NONE())];
        yield [23, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_START())];
        yield [23, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_END())];
        yield [22, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR(), Boundaries::EXCLUDE_ALL())];

        yield [1440, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_NONE())];
        yield [1439, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_START())];
        yield [1439, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_END())];
        yield [1438, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE(), Boundaries::EXCLUDE_ALL())];

        yield [86363, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_NONE())];
        yield [86362, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_START())];
        yield [86362, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_END())];
        yield [86361, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND(), Boundaries::EXCLUDE_ALL())];
    }

    /** @test */
    public function overlap_with_excluded_boundaries()
    {
        $a = Period::make('2018-01-01', '2018-01-05', boundaries: Boundaries::EXCLUDE_END());
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10', boundaries: Boundaries::EXCLUDE_START());
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertTrue($a->overlapsWith($b));
    }

    /**
     * @dataProvider boundariesForSubtract
     * @test
     */
    public function subtract_with_boundaries(string $period, string $subtract, string $result)
    {
        $this->assertEquals(
            $result,
            Period::fromString($period)
                ->subtract(Period::fromString($subtract))[0]
                ->asString()
        );
    }

    public function boundariesForSubtract(): Generator
    {
        yield [
            'period' => '[2021-01-01,2021-02-01)',      //          [=========)
            'subtract' => '[2021-01-15,2021-02-01]',    //                  [=====]
            'result' => '[2021-01-01,2021-01-15)',      //          [=======)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01)',      //          [=========)
            'subtract' => '(2021-01-15,2021-02-01]',    //                  (=====]
            'result' => '[2021-01-01,2021-01-16)',      //          [========)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01]',      //          [=========]
            'subtract' => '(2021-01-15,2021-02-01]',    //                  (=====]
            'result' => '[2021-01-01,2021-01-15]',      //          [=======]
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01]',      //          [=========]
            'subtract' => '[2021-01-15,2021-02-01]',    //                  [=====]
            'result' => '[2021-01-01,2021-01-14]',      //          [======]
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01]',      //                  [=======]
            'subtract' => '[2021-01-01,2021-01-10]',    //          [=========]
            'result' => '[2021-01-11,2021-02-01]',      //                     [====]
        ];

        yield [
            'period' => '(2021-01-01,2021-02-01]',      //                  (=======]
            'subtract' => '[2021-01-01,2021-01-10]',    //          [=========]
            'result' => '(2021-01-10,2021-02-01]',      //                    (=====]
        ];

        yield [
            'period' => '(2021-01-01,2021-02-01]',      //                  (=======]
            'subtract' => '[2021-01-01,2021-01-10)',    //          [=========)
            'result' => '(2021-01-09,2021-02-01]',      //                   (======]
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01]',      //                  [=======]
            'subtract' => '[2021-01-01,2021-01-10)',    //          [=========)
            'result' => '[2021-01-10,2021-02-01]',      //                    [=====]
        ];
    }

    /**
     * @dataProvider boundariesForOverlap
     * @test
     */
    public function overlap_with_boundaries(string $period, string $overlap, string $result)
    {
        $this->assertEquals(
            $result,
            Period::fromString($period)
                ->overlap(Period::fromString($overlap))
                ->asString()
        );
    }

    public function boundariesForOverlap(): Generator
    {
        yield [
            'period' => '[2021-01-01,2021-02-01)',     //          [==================)
            'overlap' => '[2021-01-10,2021-01-15]',    //               [=========]
            'result' => '[2021-01-10,2021-01-16)',     //               [==========)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01)',     //          [==================)
            'overlap' => '[2021-01-10,2021-01-15)',    //               [=========)
            'result' => '[2021-01-10,2021-01-15)',     //               [=========)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01)',     //          [==================)
            'overlap' => '[2021-01-10,2021-02-15)',    //                      [=========)
            'result' => '[2021-01-10,2021-02-01)',     //                      [======)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01)',     //          [==================)
            'overlap' => '[2021-01-10,2021-02-15]',    //                      [=========]
            'result' => '[2021-01-10,2021-02-01)',     //                      [======)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01)',     //          [==================)
            'overlap' => '[2021-01-01,2021-01-15]',    //     [===========]
            'result' => '[2021-01-01,2021-01-16)',     //          [=======)
        ];

        yield [
            'period' => '[2021-01-01,2021-02-01)',     //          [==================)
            'overlap' => '[2021-01-01,2021-01-15)',    //     [===========)
            'result' => '[2021-01-01,2021-01-15)',     //          [======)
        ];

        yield [
            'period' => '(2021-01-01,2021-02-01)',     //          (==================)
            'overlap' => '[2021-01-10,2021-01-15]',    //                   [======]
            'result' => '(2021-01-09,2021-01-16)',     //                  (========)
        ];

        yield [
            'period' => '(2021-01-01,2021-02-01)',     //          (==================)
            'overlap' => '(2021-01-10,2021-01-15)',    //                   (======)
            'result' => '(2021-01-10,2021-01-15)',     //                   (======)
        ];
    }
}

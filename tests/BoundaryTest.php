<?php

namespace Spatie\Period\Tests;

use DateTime;
use Spatie\Period\Period;
use Spatie\Period\Precision;
use Spatie\Period\Boundaries;
use PHPUnit\Framework\TestCase;

class BoundaryTest extends TestCase
{
    /** @test */
    public function exclude_none()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_NONE);

        $this->assertFalse($period->startExcluded());
        $this->assertFalse($period->endExcluded());
    }

    /** @test */
    public function exclude_start()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_START);

        $this->assertTrue($period->startExcluded());
        $this->assertFalse($period->endExcluded());
    }

    /** @test */
    public function exclude_end()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_END);

        $this->assertFalse($period->startExcluded());
        $this->assertTrue($period->endExcluded());
    }

    /** @test */
    public function exclude_all()
    {
        $period = Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_ALL);

        $this->assertTrue($period->startExcluded());
        $this->assertTrue($period->endExcluded());
    }

    /**
     * @test
     * @dataProvider periodsWithAmountsOfIncludedDates
     */
    public function length_with_boundaries($expectedAmount, Period $period)
    {
        $this->assertEquals($expectedAmount, $period->length());
    }

    public function periodsWithAmountsOfIncludedDates()
    {
        return [
            [4, Period::make('2016-01-01', '2019-02-05', Precision::YEAR, Boundaries::EXCLUDE_NONE)],
            [3, Period::make('2016-01-01', '2019-02-05', Precision::YEAR, Boundaries::EXCLUDE_START)],
            [3, Period::make('2016-01-01', '2019-02-05', Precision::YEAR, Boundaries::EXCLUDE_END)],
            [2, Period::make('2016-01-01', '2019-02-05', Precision::YEAR, Boundaries::EXCLUDE_ALL)],

            [43, Period::make('2016-01-01', '2019-07-05', Precision::MONTH, Boundaries::EXCLUDE_NONE)],
            [42, Period::make('2016-01-01', '2019-07-05', Precision::MONTH, Boundaries::EXCLUDE_START)],
            [42, Period::make('2016-01-01', '2019-07-05', Precision::MONTH, Boundaries::EXCLUDE_END)],
            [41, Period::make('2016-01-01', '2019-07-05', Precision::MONTH, Boundaries::EXCLUDE_ALL)],

            [31, Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_NONE)],
            [30, Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_START)],
            [30, Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_END)],
            [29, Period::make('2018-01-01', '2018-01-31', null, Boundaries::EXCLUDE_ALL)],

            [24, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR, Boundaries::EXCLUDE_NONE)],
            [23, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR, Boundaries::EXCLUDE_START)],
            [23, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR, Boundaries::EXCLUDE_END)],
            [22, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:00', Precision::HOUR, Boundaries::EXCLUDE_ALL)],

            [1440, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE, Boundaries::EXCLUDE_NONE)],
            [1439, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE, Boundaries::EXCLUDE_START)],
            [1439, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE, Boundaries::EXCLUDE_END)],
            [1438, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::MINUTE, Boundaries::EXCLUDE_ALL)],

            [86363, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND, Boundaries::EXCLUDE_NONE)],
            [86362, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND, Boundaries::EXCLUDE_START)],
            [86362, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND, Boundaries::EXCLUDE_END)],
            [86361, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:22', Precision::SECOND, Boundaries::EXCLUDE_ALL)],
        ];
    }

    /** @test */
    public function length_with_random_values()
    {
        $iteratorTime = 0;
        $calcTime = 0;

        for ($i = 0; $i < 64; $i++) {
            $date = new DateTime('2000-01-01 00:00:00');
            $date->setTimestamp($date->getTimestamp() + mt_rand(0, 126144000));
            $start = $date->format('Y-m-d H:i:s');
            $precisionIndex = mt_rand(0, 5);
            $precision = [Precision::YEAR, Precision::MONTH, Precision::DAY, Precision::HOUR, Precision::MINUTE, Precision::SECOND][$precisionIndex];
            $precisionFactor = [365 * 24 * 3600, 30 * 24 * 3600, 24 * 3600, 3600, 60, 1][$precisionIndex];
            $date->setTimestamp($date->getTimestamp() + (mt_rand(0, 1) ? mt_rand(3, 200) : mt_rand(1000, 2000)) * $precisionFactor);
            $bound = [Boundaries::EXCLUDE_NONE, Boundaries::EXCLUDE_START, Boundaries::EXCLUDE_END, Boundaries::EXCLUDE_ALL][mt_rand(0, 3)];
            $period = Period::make($start, $date->format('Y-m-d H:i:s'), $precision, $bound);

            $iteratorTime -= microtime(true);
            $iteratorCount = iterator_count($period);
            $iteratorTime += microtime(true);

            $calcTime -= microtime(true);
            $length = $period->length();
            $calcTime += microtime(true);

            $this->assertEquals($iteratorCount, $length);
        }

        $this->assertLessThan($iteratorTime, $calcTime);
    }

    /** @test */
    public function overlap_with_excluded_boundaries()
    {
        $a = Period::make('2018-01-01', '2018-01-05', null, Boundaries::EXCLUDE_END);
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10', null, Boundaries::EXCLUDE_START);
        $this->assertFalse($a->overlapsWith($b));

        $a = Period::make('2018-01-01', '2018-01-05');
        $b = Period::make('2018-01-05', '2018-01-10');
        $this->assertTrue($a->overlapsWith($b));
    }
}

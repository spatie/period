<?php

declare(strict_types=1);

namespace Spatie\Period\Tests;

use Spatie\Period\Period;
use Spatie\Period\Precision;
use Spatie\Period\Boundaries;
use PHPUnit\Framework\TestCase;
use Spatie\Period\PeriodDuration;

class PeriodDurationTest extends TestCase
{
    /**
     * @test
     * @dataProvider sames
     */
    public function it_is_the_same_as(Period $a, Period $b)
    {
        $duration = new PeriodDuration($a);
        $other = new PeriodDuration($b);

        $this->assertTrue($duration->equals($other));
        $this->assertFalse($duration->isLargerThan($other));
        $this->assertFalse($duration->isSmallerThan($other));
        $this->assertSame(0, $duration->compareTo($other));
    }

    public function sames()
    {
        return [
            [
                Period::make('2019-04-01', '2019-04-02'),
                Period::make('2019-04-01', '2019-04-02'),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider equals
     */
    public function it_is_equal_but_not_the_same(Period $a, Period $b)
    {
        $duration = new PeriodDuration($a);
        $other = new PeriodDuration($b);

        $this->assertTrue($duration->equals($other));
        $this->assertFalse($duration->isLargerThan($other));
        $this->assertFalse($duration->isSmallerThan($other));
        $this->assertSame(0, $duration->compareTo($other));
    }

    public function equals()
    {
        return [
            '1 year' => [ // P1Y, but P365T vs P366T (2020 is a leap year)
                Period::make('2019-01-01', '2019-12-31', Precision::YEAR),
                Period::make('2020-01-01', '2020-12-31', Precision::YEAR),
            ],
            '1 month' => [ // P1M , but P30D vs P28D
                Period::make('2019-04-01', '2019-04-30', Precision::MONTH),
                Period::make('2019-02-01', '2019-02-28', Precision::MONTH),
            ],
            '1 day' => [ // P1D , but but different days
                Period::make('2019-04-23', '2019-04-23', Precision::DAY),
                Period::make('2019-04-24', '2019-04-24', Precision::DAY),
            ],
            '12 hours' => [ // PT12H, but on different halves of the day
                Period::make('2019-04-23 00:00:00', '2019-04-23 11:59:00', Precision::HOUR),
                Period::make('2019-04-23 12:00:00', '2019-04-23 23:59:00', Precision::HOUR),
            ],
            '30 minutes' => [ // PT30M, but on different halves of the hour
                Period::make('2019-04-23 11:45:00', '2019-04-23 12:14:59', Precision::MINUTE),
                Period::make('2019-04-23 12:15:00', '2019-04-23 12:44:59', Precision::MINUTE),
            ],
            '30 seconds' => [ // PT30S, but on different halves of the minute
                Period::make('2019-04-23 11:59:45', '2019-04-23 12:00:14', Precision::SECOND),
                Period::make('2019-04-23 12:00:15', '2019-04-23 12:00:44', Precision::SECOND),
            ],
            'same amount of days within a month' => [ // P4D == P4D
                Period::make('2019-02-01', '2019-02-28', Precision::DAY),
                Period::make('2019-04-01', '2019-04-28', Precision::DAY),
            ],
            'same amount of days spanning two months' => [ // P4D is still P4D
                Period::make('2019-02-27', '2019-03-02', Precision::DAY),
                Period::make('2019-03-30', '2019-04-02', Precision::DAY),
            ],
            'same period, different precision' => [ // in this case P1D == P24H
                Period::make('2019-04-23', '2019-04-23', Precision::DAY),
                Period::make('2019-04-23', '2019-04-23', Precision::HOUR),
            ],
            'different boundaries, but same amount of days, ' => [ // P1D == P1D
                Period::make('2019-04-23', '2019-04-23', Precision::DAY, Boundaries::EXCLUDE_NONE),
                Period::make('2019-04-23', '2019-04-24', Precision::DAY, Boundaries::EXCLUDE_END),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider differents
     */
    public function it_is_different(Period $a, Period $b)
    {
        $duration = new PeriodDuration($a);
        $other = new PeriodDuration($b);

        $this->assertFalse($duration->equals($other));
    }

    public function differents()
    {
        return [
            'a is smaller than b' => [
                Period::make('2019-04-23', '2019-04-24', Precision::DAY),
                Period::make('2019-04-23', '2019-04-25', Precision::DAY),
            ],
            'b is smaller than a' => [
                Period::make('2019-04-23', '2019-04-25', Precision::DAY),
                Period::make('2019-04-23', '2019-04-24', Precision::DAY),
            ],
        ];
    }
}

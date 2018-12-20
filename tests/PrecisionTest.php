<?php

namespace Spatie\Period\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Boundaries;
use Spatie\Period\Exceptions\CannotComparePeriods;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class PrecisionTest extends TestCase
{
    /**
     * @test
     * @dataProvider roundingDates
     */
    public function dates_are_rounded_on_precision(
        int $precision,
        string $expectedStart,
        string $expectedEnd
    ) {
        $period = Period::make(
            '2018-02-05 11:11:11',
            '2018-03-05 11:11:11',
            null,
            Boundaries::EXCLUDE_NONE,
            $precision
        );

        $this->assertEquals(
            DateTime::createFromFormat('Y-m-d H:i:s', $expectedStart),
            $period->getStart()
        );

        $this->assertEquals(
            DateTime::createFromFormat('Y-m-d H:i:s', $expectedEnd),
            $period->getEnd()
        );
    }

    public function roundingDates(): array
    {
        return [
            [Precision::YEAR, '2018-01-01 00:00:00', '2018-01-01 00:00:00'],
            [Precision::MONTH, '2018-02-01 00:00:00', '2018-03-01 00:00:00'],
            [Precision::DAY, '2018-02-05 00:00:00', '2018-03-05 00:00:00'],
            [Precision::HOUR, '2018-02-05 11:00:00', '2018-03-05 11:00:00'],
            [Precision::MINUTE, '2018-02-05 11:11:00', '2018-03-05 11:11:00'],
            [Precision::SECOND, '2018-02-05 11:11:11', '2018-03-05 11:11:11'],
        ];
    }

    /** @test */
    public function comparing_two_periods_with_different_precision_is_not_allowed()
    {
        $a = Period::make('2018-01-01', '2018-01-01', null, Boundaries::EXCLUDE_NONE, Precision::MONTH);
        $b = Period::make('2018-01-01', '2018-01-01', null, Boundaries::EXCLUDE_NONE, Precision::DAY);

        $this->expectException(CannotComparePeriods::class);

        $a->overlapsWith($b);
    }
}

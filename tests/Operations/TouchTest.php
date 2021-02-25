<?php

namespace Spatie\Period\Tests\Operations;

use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class TouchTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_two_periods_touch_each_other()
    {
        $this->assertTrue(
            Period::make('2018-01-01', '2018-01-01')
                ->touchesWith(Period::make('2018-01-02', '2018-01-02'))
        );

        $this->assertTrue(
            Period::make('2018-01-02', '2018-01-02')
                ->touchesWith(Period::make('2018-01-01', '2018-01-01'))
        );

        $this->assertFalse(
            Period::make('2018-01-01', '2018-01-01')
                ->touchesWith(Period::make('2018-01-03', '2018-01-03'))
        );

        $this->assertFalse(
            Period::make('2018-01-03', '2018-01-03')
                ->touchesWith(Period::make('2018-01-01', '2018-01-01'))
        );

        $this->assertFalse(
            Period::make('2018-01-01 06:30:00', '2018-01-01 07:30:00', Precision::HOUR())
                ->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::HOUR()))
        );

        $this->assertTrue(
            Period::make('2018-01-01 06:30:00', '2018-01-01 08:30:00', Precision::HOUR())
                ->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::HOUR()))
        );

        $this->assertFalse(
            Period::make('2018-01-01 06:30:00', '2018-01-01 07:30:00', Precision::SECOND())
                ->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::SECOND()))
        );
    }
}

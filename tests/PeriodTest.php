<?php

namespace Spatie\Period\Tests;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Boundaries;
use Spatie\Period\Exceptions\CannotCeilLowerPrecision;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class PeriodTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_period_length()
    {
        $period = Period::make('2018-01-01', '2018-01-15');

        $this->assertEquals(15, $period->length());
    }

    /** @test */
    public function it_can_renew_a_period()
    {
        $period = Period::make('2018-01-01', '2018-01-15');

        $renewal = $period->renew();

        $this->assertTrue($renewal->touchesWith($period));
        $this->assertEquals($renewal->length(), $period->length());
    }

    /** @test */
    public function it_has_a_duration()
    {
        $a = Period::make('2018-01-01', '2018-01-15');
        $b = Period::make('2018-02-01', '2018-02-15');

        $this->assertTrue($a->duration()->equals($b->duration()));
    }

    /** @test */
    public function it_accepts_carbon_instances()
    {
        $a = Period::make(Carbon::make('2018-01-01'), Carbon::make('2018-01-02'));

        $this->assertTrue($a->equals(Period::make('2018-01-01', '2018-01-02')));
    }

    /** @test */
    public function it_will_preserve_the_time()
    {
        $period = Period::make('2018-01-01 01:02:03', '2018-01-02 04:05:06');

        $this->assertTrue($period->equals(
            Period::make(
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 01:02:03'),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 04:05:06')
            )
        ));
    }

    /** @test */
    public function it_will_use_the_start_of_day_when_passing_strings_to_a_period()
    {
        $period = Period::make('2018-01-01', '2018-01-02');

        $this->assertTrue($period->equals(
            Period::make(
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00'),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')
            )
        ));
    }

    /**
     * @test
     * @dataProvider expectedPeriodLengths
     */
    public function it_is_iterable(int $expectedCount, Period $period)
    {
        $this->assertSame($expectedCount, iterator_count($period));
    }

    public function expectedPeriodLengths(): Generator
    {
        yield [1, Period::make('2018-01-01', '2018-01-01')];
        yield [15, Period::make('2018-01-01', '2018-01-15')];
        yield [14, Period::make('2018-01-01', '2018-01-15', boundaries: Boundaries::EXCLUDE_START())];
        yield [14, Period::make('2018-01-01', '2018-01-15', boundaries: Boundaries::EXCLUDE_END())];
        yield [13, Period::make('2018-01-01', '2018-01-15', boundaries: Boundaries::EXCLUDE_ALL())];
        yield [24, Period::make('2018-01-01 00:00:00', '2018-01-01 23:59:59', Precision::HOUR())];
        yield [24, Period::make('2018-01-01 00:00:00', '2018-01-02 00:00:00', Precision::HOUR(), Boundaries::EXCLUDE_END())];
    }

    /** @test */
    public function its_iterator_returns_immutable_dates()
    {
        $period = Period::make('2018-01-01', '2018-01-15');

        $current = $period->getIterator()->start;

        $this->assertInstanceOf(DateTimeImmutable::class, $current);
    }

    /** @test */
    public function it_keeps_timezone_when_boundaries_are_timezoned()
    {
        $timeZone = new DateTimeZone('Europe/London');
        $start = new DateTimeImmutable('2000-01-01', $timeZone);
        $end = new DateTimeImmutable('2000-02-01', $timeZone);
        $period = Period::make($start, $end);
        $this->assertEquals($period->start()->getTimezone(), $timeZone);
        $this->assertEquals($period->end()->getTimezone(), $timeZone);
    }

    /**
     * @test
     * @dataProvider ceilingDates
    */
    public function it_gets_the_correct_ceiling_of_a_precision(Period $period, Precision $precision, Carbon $expected)
    {
        $this->assertEquals($expected->startOfSecond(), $period->ceilingEnd($precision));
    }

    public function ceilingDates(): Generator
    {
        // Test inclusive Period's at same precision
        yield [
            Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::SECOND()),
            Precision::SECOND(),
            Carbon::make('2018-01-15 11:30:15')->endOfSecond(),
        ];

        yield [
            Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::MINUTE()),
            Precision::MINUTE(),
            Carbon::make('2018-01-15 11:30:15')->endOfMinute(),
        ];

        yield [
            Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::HOUR()),
            Precision::HOUR(),
            Carbon::make('2018-01-15 11:30:15')->endOfHour(),
        ];

        yield [
            Period::make('2018-01-01', '2018-01-15', Precision::DAY()),
            Precision::DAY(),
            Carbon::make('2018-01-15')->endOfDay(),
        ];

        yield [
            Period::make('2018-01-01', '2018-01-15', Precision::MONTH()),
            Precision::MONTH(),
            Carbon::make('2018-01-15')->endOfMonth(),
        ];

        yield [
            Period::make('2018-01-01', '2018-01-15', Precision::YEAR()),
            Precision::YEAR(),
            Carbon::make('2018-01-15')->endOfYear(),
        ];

        // Test higher precision
        yield [
            Period::make('2018-01-01', '2018-01-15', Precision::DAY()),
            Precision::MONTH(),
            Carbon::make('2018-01-15')->endOfMonth(),
        ];

        // Test exclusive period
        yield [
            Period::make('2018-01-01', '2018-01-15', Precision::DAY(), Boundaries::EXCLUDE_END()),
            Precision::DAY(),
            Carbon::make('2018-01-15')->subDay()->endOfDay(),
        ];
    }

    /** @test */
    public function it_throws_if_trying_to_get_a_ceiling_of_a_lower_precision()
    {
        $seconds = Period::make('2018-01-01 11:30:15', '2018-01-15 11:30:15', Precision::HOUR());

        $this->expectException(CannotCeilLowerPrecision::class);
        $this->expectExceptionMessage("Cannot get the latest hour of a second.");

        $seconds->ceilingEnd(Precision::SECOND());
    }
}

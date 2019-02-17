<?php

declare(strict_types=1);

namespace Spatie\Period\Tests;

use DateInterval;
use Spatie\Period\Period;
use Spatie\Period\Duration;
use Spatie\Period\Precision;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Spatie\Period\PeriodCollection;

class DurationTest extends TestCase
{
    /**
     * @test
     * @dataProvider validDurations
     */
    public function it_can_be_made($expectedLength, $expectedPrecision, $value)
    {
        $duration = Duration::make($value);

        $this->assertSame($expectedLength, $duration->length($expectedPrecision));
    }

    public function validDurations()
    {
        $weekPeriod = Period::make('2018-01-01', '2018-01-07');
        $weekAsCollection = new PeriodCollection(
            Period::make('2018-01-01', '2018-01-02'),
            Period::make('2018-01-03', '2018-01-04'),
            Period::make('2018-01-05', '2018-01-07')
        );

        return [
            [7, Precision::DAY, '1 week'],
            [7, Precision::DAY, 'P7D'],
            [7, Precision::DAY, new DateInterval('P1W')],

            [7, Precision::DAY, $weekPeriod],
            [7 * 24, Precision::HOUR, $weekPeriod],
            [7 * 24 * 60, Precision::MINUTE, $weekPeriod],
            [7 * 24 * 60 * 60, Precision::SECOND, $weekPeriod],

            [7, Precision::DAY, $weekAsCollection],
        ];
    }

    /**
     * @test
     * @dataProvider periodsWithPrecisions
     */
    public function it_uses_the_precision_of_a_given_period($expectedPrecision, Period $period)
    {
        $duration = Duration::fromPeriod($period);
        $this->assertSame($expectedPrecision, $duration->precision());
    }

    public function periodsWithPrecisions()
    {
        return [
            [Precision::SECOND, Period::make('2018-01-01', '2018-01-02', Precision::SECOND)],
            [Precision::MINUTE, Period::make('2018-01-01', '2018-01-02', Precision::MINUTE)],
            [Precision::HOUR, Period::make('2018-01-01', '2018-01-02', Precision::HOUR)],
            [Precision::DAY, Period::make('2018-01-01', '2018-01-02', Precision::DAY)],
        ];
    }

    /**
     * @test
     * @dataProvider durationsWithPrecisions
     */
    public function it_can_determine_a_precision($expectedPrecision, $value)
    {
        $this->assertSame($expectedPrecision, Duration::make($value)->precision());
    }

    public function durationsWithPrecisions()
    {
        return [
            [Precision::SECOND, '61 seconds'],
            [Precision::MINUTE, '61 minutes'],
            [Precision::HOUR, '25 hours'],
            [Precision::DAY, '1 week'],
            [Precision::DAY, '1 month'],
            [Precision::DAY, '1 year'],
        ];
    }

    /** @test */
    public function it_can_be_compared()
    {
        $oneMinute = Duration::make('1 minute');
        $sixtySeconds = Duration::make('60 seconds');
        $none = Duration::none();

        $this->assertTrue($oneMinute->equals($sixtySeconds));
        $this->assertTrue($none->isSmallerThan($oneMinute));
        $this->assertTrue($oneMinute->isLargerThan($none));
    }

    /**
     * @test
     * @dataProvider unsupportedPrecisions
     */
    public function it_does_not_support_precisions_with_variable_values(int $precision)
    {
        $this->expectException(InvalidArgumentException::class);
        Duration::fromPeriod(Period::make('2018-01-01', '2018-01-31', $precision));
    }

    /**
     * @test
     * @dataProvider unsupportedPrecisions
     */
    public function its_length_can_not_be_retrieved_with_an_unsupported_precision(int $precision)
    {
        $duration = Duration::make('1337 seconds'); // doesn't matter

        $this->expectException(InvalidArgumentException::class);
        $duration->length($precision);
    }

    public function unsupportedPrecisions()
    {
        return [
            [Precision::MONTH],
            [Precision::YEAR],
        ];
    }
}

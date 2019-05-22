<?php declare(strict_types=1);

namespace Spatie\Period\Tests;

use PHPUnit\Framework\TestCase;

class DateTimeExtensionTest extends TestCase
{
    /** @test */
    public function it_should_be_possible_use_date_time_extensions() : void
    {
        $start = new DateTimeExtension('2019-05-22');
        $end = new DateTimeExtension('2019-06-05');
        $period = new TestPeriod($start, $end);

        $this->assertInstanceOf(DateTimeExtension::class, $period->getStart());
        $this->assertInstanceOf(DateTimeExtension::class, $period->getEnd());
        $this->assertInstanceOf(DateTimeExtension::class, $period->getIncludedStart());
        $this->assertInstanceOf(DateTimeExtension::class, $period->getIncludedEnd());
    }

    /** @test */
    public function it_should_be_possible_to_use_period_extension_to_force_date_time_extension() : void
    {
        $period = TestPeriod::make('2019-05-01', '2019-05-31');

        $this->assertInstanceOf(TestPeriod::class, $period);
        $this->assertInstanceOf(DateTimeExtension::class, $period->getStart());
    }
}

/**
 * In real life this would be Carbon or Chronos
 */
class DateTimeExtension extends \DateTimeImmutable
{
    public static function instance(\DateTimeImmutable $dt): self
    {
        return new static($dt->format('Y-m-d H:i:s.u'), $dt->getTimezone());
    }
}

/**
 * @method DateTimeExtension getStart
 * @method DateTimeExtension getIncludedStart
 * @method DateTimeExtension getEnd
 * @method DateTimeExtension getIncludedEnd
 */
class TestPeriod extends \Spatie\Period\Period
{
    /** @var DateTimeExtension */
    protected $start;
    /** @var DateTimeExtension */
    protected $end;

    public function __construct(DateTimeExtension $start, DateTimeExtension $end, ?int $precisionMask = null, ?int $boundaryExclusionMask = null)
    {
        parent::__construct($start, $end, $precisionMask, $boundaryExclusionMask);
    }

    /** @return DateTimeExtension */
    protected static function resolveDate($date, ?string $format): \DateTimeImmutable
    {
        return DateTimeExtension::instance(parent::resolveDate($date, $format));
    }

    /** @return DateTimeExtension */
    protected function roundDate(\DateTimeInterface $date, int $precision): \DateTimeImmutable
    {
        return DateTimeExtension::instance(parent::roundDate($date, $precision));
    }
}

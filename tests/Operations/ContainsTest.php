<?php

namespace Spatie\Period\Tests\Operations;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class ContainsTest extends TestCase
{
    /**
     * @test
     *
     * A    [===============]
     * B        |
     * C  |
     * D                        |
     */
    public function it_can_determine_whether_a_period_contains_a_date()
    {
        $period = Period::make('2018-01-01', '2018-01-31');

        $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-01')));
        $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-31')));
        $this->assertTrue($period->contains(new DateTimeImmutable('2018-01-10')));

        $this->assertFalse($period->contains(new DateTimeImmutable('2017-12-31')));
        $this->assertFalse($period->contains(new DateTimeImmutable('2018-02-01')));
    }

    /**
     * @test
     *
     * A    [===============]
     * B        [====]
     */
    public function contains_with_other_period()
    {
        $period = Period::make('2020-01-01', '2020-01-31');

        $this->assertTrue($period->contains(Period::make('2020-01-10', '2020-01-11')));
        $this->assertTrue($period->contains($period));
        $this->assertFalse($period->contains(Period::make('2020-01-10', '2020-02-11')));
        $this->assertFalse($period->contains(Period::make('2019-01-10', '2020-02-11')));
        $this->assertFalse($period->contains(Period::make('2019-01-10', '2020-01-10')));
    }

    /** @test */
    public function precision_is_kept_when_testing_contains()
    {
        $a = Period::make('2018-01-01', '2018-01-31', Precision::DAY);

        $this->assertTrue($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 00:00:00')));
        $this->assertTrue($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00')));
        $this->assertTrue($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-31 23:59:59')));

        $this->assertFalse($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-02-01 00:00:00')));
        $this->assertFalse($a->contains(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2017-12-21 23:59:59')));
    }
}

<?php

namespace Spatie\Period\Tests\Operations;

use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;

class DiffTest extends TestCase
{
    /**
     * @test
     *
     * A        [=======]
     * B                   [======]
     *
     * DIFF     [=======]  [======]
     */
    public function diff_with_no_overlap()
    {
        $a = Period::make('2020-01-01', '2020-01-15');
        $b = Period::make('2020-02-01', '2020-02-15');

        $diff = $a->diff($b);

        $this->assertCount(2, $diff);
        $this->assertTrue($diff[0]->equals($a));
        $this->assertTrue($diff[1]->equals($b));
    }

    /**
     * @test
     *
     * A        [=======]
     * B              [=======]
     *
     * DIFF     [====]   [====]
     */
    public function diff_right()
    {
        $a = Period::make('2020-01-01', '2020-01-31');

        $b = Period::make('2020-01-15', '2020-02-15');

        $diff = $a->diff($b);

        $this->assertCount(2, $diff);
        $this->assertTrue($diff[0]->equals(Period::make('2020-01-01', '2020-01-14')));
        $this->assertTrue($diff[1]->equals(Period::make('2020-02-01', '2020-02-15')));
    }

    /**
     * @test
     *
     * A             [=======]
     * B        [=======]
     *
     * DIFF     [====]   [====]
     */
    public function diff_left()
    {
        $a = Period::make('2020-01-15', '2020-02-15');
        $b = Period::make('2020-01-01', '2020-01-31');

        $diff = $a->diff($b);

        $this->assertCount(2, $diff);
        $this->assertTrue($diff[0]->equals(Period::make('2020-01-01', '2020-01-14')));
        $this->assertTrue($diff[1]->equals(Period::make('2020-02-01', '2020-02-15')));
    }

    /**
     * @test
     *
     * A        [=============================]
     * B            [========]
     *
     * DIFF     [==]          [===============]
     */
    public function diff_within()
    {
        $a = Period::make('2020-01-01', '2020-01-31');
        $b = Period::make('2020-01-10', '2020-01-15');

        $diffs = $a->diff($b);

        $this->assertCount(2, $diffs);
        $this->assertTrue($diffs[0]->equals(Period::make('2020-01-01', '2020-01-09')));
        $this->assertTrue($diffs[1]->equals(Period::make('2020-01-16', '2020-01-31')));
    }

    /**
     * @test
     *
     * A            [========]
     * B        [=============================]
     *
     * DIFF     [==]          [===============]
     */
    public function diff_within_reverse()
    {
        $a = Period::make('2020-01-10', '2020-01-15');
        $b = Period::make('2020-01-01', '2020-01-31');

        $diffs = $a->diff($b);

        $this->assertCount(2, $diffs);
        $this->assertTrue($diffs[0]->equals(Period::make('2020-01-01', '2020-01-09')));
        $this->assertTrue($diffs[1]->equals(Period::make('2020-01-16', '2020-01-31')));
    }
}

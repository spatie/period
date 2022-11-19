<?php

use Spatie\Period\Exceptions\CannotComparePeriods;
use Spatie\Period\Period;
use Spatie\Period\Precision;

it('dates are rounded on precision', function (Precision $precision, string $expectedStart, string $expectedEnd) {
    $period = Period::make('2018-02-05 11:11:11', '2018-03-05 11:11:11', $precision);

    $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', $expectedStart), $period->start());

    $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', $expectedEnd), $period->end());
})->with('rounding_dates');

it('comparing two periods with different precision is not allowed', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::MONTH());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::DAY());

    $this->expectException(CannotComparePeriods::class);

    $a->overlapsWith($b);
});

it('precision with seconds', function () {
    $a = Period::make('2018-01-01 00:00:15', '2018-01-01 00:00:15', Precision::SECOND());
    $b = Period::make('2018-01-01 00:00:15', '2018-01-01 00:00:15', Precision::SECOND());
    $c = Period::make('2018-01-01 00:00:16', '2018-01-01 00:00:16', Precision::SECOND());

    $this->assertTrue($a->overlapsWith($b));
    $this->assertFalse($a->overlapsWith($c));
});

it('precision with minutes', function () {
    $a = Period::make('2018-01-01 00:15:00', '2018-01-01 00:15:00', Precision::MINUTE());
    $b = Period::make('2018-01-01 00:15:00', '2018-01-01 00:15:00', Precision::MINUTE());
    $c = Period::make('2018-01-01 00:16:00', '2018-01-01 00:16:00', Precision::MINUTE());

    $this->assertTrue($a->overlapsWith($b));
    $this->assertFalse($a->overlapsWith($c));
});

it('precision with hours', function () {
    $a = Period::make('2018-01-01 15:00:00', '2018-01-01 15:00:00', Precision::HOUR());
    $b = Period::make('2018-01-01 15:00:00', '2018-01-01 15:00:00', Precision::HOUR());
    $c = Period::make('2018-01-01 16:00:00', '2018-01-01 16:00:00', Precision::HOUR());

    $this->assertTrue($a->overlapsWith($b));
    $this->assertFalse($a->overlapsWith($c));
});

it('precision with days', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::DAY());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::DAY());
    $c = Period::make('2018-01-02', '2018-01-02', Precision::DAY());

    $this->assertTrue($a->overlapsWith($b));
    $this->assertFalse($a->overlapsWith($c));
});

it('precision with months', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::MONTH());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::MONTH());
    $c = Period::make('2018-02-01', '2018-02-01', Precision::MONTH());

    $this->assertTrue($a->overlapsWith($b));
    $this->assertFalse($a->overlapsWith($c));
});

it('precision with years', function () {
    $a = Period::make('2018-01-01', '2018-01-01', Precision::YEAR());
    $b = Period::make('2018-01-01', '2018-01-01', Precision::YEAR());
    $c = Period::make('2019-01-01', '2019-01-01', Precision::YEAR());

    $this->assertTrue($a->overlapsWith($b));
    $this->assertFalse($a->overlapsWith($c));
});

it('precision is kept when comparing with the ranges start', function () {
    $a = Period::make('2018-01-01 11:11:11', '2018-01-31', Precision::DAY());

    $boundaryDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-01 11:11:11');

    $this->assertTrue($a->startsAt($boundaryDate));

    $includedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-02 00:00:00');

    $this->assertTrue($a->startsBefore($includedDate));
    $this->assertTrue($a->startsBeforeOrAt($includedDate));
    $this->assertFalse($a->startsAfter($includedDate));
    $this->assertFalse($a->startsAfterOrAt($includedDate));

    $excludedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2017-12-31 23:59:59');

    $this->assertFalse($a->startsBefore($excludedDate));
    $this->assertFalse($a->startsBeforeOrAt($excludedDate));
    $this->assertTrue($a->startsAfter($excludedDate));
    $this->assertTrue($a->startsAfterOrAt($excludedDate));
});

it('precision is kept when comparing with the ranges end', function () {
    $a = Period::make('2018-01-01', '2018-01-31', Precision::DAY());

    $boundaryDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-31 23:59:59');

    $this->assertTrue($a->endsAt($boundaryDate));

    $includedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-01-30 23:59:59');

    $this->assertTrue($a->endsAfter($includedDate));
    $this->assertTrue($a->endsAfterOrAt($includedDate));
    $this->assertFalse($a->endsBefore($includedDate));
    $this->assertFalse($a->endsBeforeOrAt($includedDate));

    $excludedDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-02-01 00:00:00');

    $this->assertTrue($a->endsBefore($excludedDate));
    $this->assertTrue($a->endsBeforeOrAt($excludedDate));
    $this->assertFalse($a->endsAfter($excludedDate));
    $this->assertFalse($a->endsAfterOrAt($excludedDate));
});

it('precision is kept with subtract', function () {
    $a = Period::make('2018-01-05 00:00:00', '2018-01-10 00:00:00', Precision::MINUTE());
    $b = Period::make('2018-01-15 00:00:00', '2018-03-01 00:00:00', Precision::MINUTE());

    [$diff] = $a->subtract($b);

    $this->assertEquals(Precision::MINUTE(), $diff->precision());
});

it('precision is kept with overlap', function () {
    $a = Period::make('2018-01-05 00:00:00', '2018-01-10 00:00:00', Precision::MINUTE());
    $b = Period::make('2018-01-01 00:00:00', '2018-01-31 00:00:00', Precision::MINUTE());

    [$diff] = $a->overlapAny($b);

    $this->assertEquals(Precision::MINUTE(), $diff->precision());
});

it('precision is kept with gap', function () {
    $a = Period::make('2018-01-05 00:00:00', '2018-01-10 00:00:00', Precision::MINUTE());
    $b = Period::make('2018-01-15 00:00:00', '2018-01-31 00:00:00', Precision::MINUTE());

    $gap = $a->gap($b);

    $this->assertEquals(Precision::MINUTE(), $gap->precision());
});

it('precision seconds is more precise than hours', function () {
    $hours = Precision::HOUR();
    $seconds = Precision::SECOND();

    $this->assertTrue($seconds->higherThan($hours));
});

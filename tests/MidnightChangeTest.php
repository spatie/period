<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;

/*
 * Some time zones change the clock at midnight. When the clock goes forward, midnight does not exist
 * and the day starts at 01:00. When the clock goes back, midnight occurs twice.
 *
 * Every test below uses periods with a day precision around such a day. The result must be the same
 * as around any other day.
 */
dataset('days with a midnight change', [
    'Havana, no midnight' => ['America/Havana', '2020-03-08'],
    'Beirut, no midnight' => ['Asia/Beirut', '2020-03-29'],
    'Santiago, no midnight' => ['America/Santiago', '2020-09-06'],
    'Azores, no midnight' => ['Atlantic/Azores', '2020-03-29'],
    'Tehran, no midnight' => ['Asia/Tehran', '2022-03-22'],
    'Sao Paulo, no midnight' => ['America/Sao_Paulo', '2018-11-04'],
    'Asuncion, no midnight' => ['America/Asuncion', '2020-10-04'],
    'Gaza, no midnight' => ['Asia/Gaza', '2020-03-28'],
    'Amman, no midnight' => ['Asia/Amman', '2020-03-27'],
    'Havana, two midnights' => ['America/Havana', '2020-11-01'],
    'Azores, two midnights' => ['Atlantic/Azores', '2020-10-25'],
    'Gaza, two midnights' => ['Asia/Gaza', '2020-10-24'],
    'Amman, two midnights' => ['Asia/Amman', '2020-10-30'],
]);

/**
 * A period from $from to $to, both a number of days from the day with the midnight change.
 */
function midnightChangePeriod(string $timezone, string $day, int $from, int $to, ?Boundaries $boundaries = null): Period
{
    $noon = new DateTimeImmutable("{$day} 12:00:00", new DateTimeZone($timezone));

    return Period::make($noon->modify("{$from} day"), $noon->modify("{$to} day"), null, $boundaries);
}

function midnightChangeDays(Period | PeriodCollection | null $periods): array
{
    if ($periods === null) {
        return [];
    }

    return array_map(
        fn (Period $period) => $period->includedStart()->format('Y-m-d H:i') . ' > ' . $period->includedEnd()->format('Y-m-d H:i'),
        $periods instanceof Period ? [$periods] : iterator_to_array($periods, false),
    );
}

it('has a skipped or a repeated midnight on the tested day', function (string $timezone, string $day) {
    $zone = new DateTimeZone($timezone);
    $start = new DateTimeImmutable("{$day} 00:00:00", $zone);
    $otherMidnights = array_filter(
        [$start->getTimestamp() - 3600, $start->getTimestamp() + 3600],
        fn (int $timestamp) => (new DateTimeImmutable("@{$timestamp}"))->setTimezone($zone)->format('Y-m-d H') === "{$day} 00",
    );

    $skipped = $start->format('H:i') === '01:00';
    $repeated = count($otherMidnights) === 1;

    expect($skipped || $repeated)->toBeTrue();
})->with('days with a midnight change');

it('excludes a boundary on the day of a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    expect($period(0, 2, Boundaries::EXCLUDE_START())->equals($period(1, 2)))->toBeTrue();
    expect($period(-2, 0, Boundaries::EXCLUDE_END())->equals($period(-2, -1)))->toBeTrue();
    expect($period(-1, 1, Boundaries::EXCLUDE_ALL())->equals($period(0, 0)))->toBeTrue();
    expect($period(-1, 2, Boundaries::EXCLUDE_START())->equals($period(0, 2)))->toBeTrue();
    expect($period(-2, 1, Boundaries::EXCLUDE_END())->equals($period(-2, 0)))->toBeTrue();
})->with('days with a midnight change');

it('contains the dates next to an excluded day of a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);
    $date = fn (int $offset) => $period($offset, $offset)->start();

    $after = $period(0, 2, Boundaries::EXCLUDE_START());

    expect($after->contains($date(0)))->toBeFalse();
    expect($after->contains($date(1)))->toBeTrue();
    expect($after->contains($period(1, 2)))->toBeTrue();

    $before = $period(-2, 0, Boundaries::EXCLUDE_END());

    expect($before->contains($date(0)))->toBeFalse();
    expect($before->contains($date(-1)))->toBeTrue();
    expect($before->contains($period(-2, -1)))->toBeTrue();
})->with('days with a midnight change');

it('counts the days of a period around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    expect($period(0, 0)->length())->toBe(1);
    expect($period(0, 1)->length())->toBe(2);
    expect($period(0, 3)->length())->toBe(4);
    expect($period(-1, 0)->length())->toBe(2);
    expect($period(-1, 1)->length())->toBe(3);
    expect($period(-3, 3)->length())->toBe(7);
    expect($period(0, 2, Boundaries::EXCLUDE_START())->length())->toBe(2);
    expect($period(-2, 0, Boundaries::EXCLUDE_END())->length())->toBe(2);
    expect($period(-1, 1, Boundaries::EXCLUDE_ALL())->length())->toBe(1);
})->with('days with a midnight change');

it('compares the duration of periods around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    $oneDay = $period(10, 10)->duration();
    $twoDays = $period(10, 11)->duration();

    $periods = [
        $period(0, 1),
        $period(-1, 0),
        $period(0, 2, Boundaries::EXCLUDE_START()),
        $period(-2, 0, Boundaries::EXCLUDE_END()),
    ];

    foreach ($periods as $twoDayPeriod) {
        expect($twoDayPeriod->duration()->equals($twoDays))->toBeTrue();
        expect($twoDayPeriod->duration()->compareTo($twoDays))->toBe(0);
        expect($twoDayPeriod->duration()->equals($oneDay))->toBeFalse();
        expect($twoDayPeriod->duration()->isLargerThan($oneDay))->toBeTrue();
        expect($twoDayPeriod->duration()->isLargerThan($twoDays))->toBeFalse();
        expect($twoDayPeriod->duration()->isSmallerThan($twoDays))->toBeFalse();
    }

    expect($period(0, 0)->duration()->equals($oneDay))->toBeTrue();
    expect($period(0, 0)->duration()->isSmallerThan($twoDays))->toBeTrue();
})->with('days with a midnight change');

it('renews a period around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    expect($period(0, 1)->renew()->equals($period(2, 3)))->toBeTrue();
    expect($period(-2, -1)->renew()->equals($period(0, 1)))->toBeTrue();
    expect($period(-1, 0)->renew()->equals($period(1, 2)))->toBeTrue();
    expect($period(-3, 0)->renew()->equals($period(1, 4)))->toBeTrue();
    expect($period(0, 0)->renew()->equals($period(1, 1)))->toBeTrue();
})->with('days with a midnight change');

it('compares periods around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    expect($period(-2, 1)->overlapsWith($period(0, 1, Boundaries::EXCLUDE_START())))->toBeTrue();
    expect($period(-2, 0)->overlapsWith($period(0, 2, Boundaries::EXCLUDE_START())))->toBeFalse();
    expect($period(0, 2)->overlapsWith($period(-2, 0, Boundaries::EXCLUDE_END())))->toBeFalse();

    expect($period(-2, 0)->touchesWith($period(0, 2, Boundaries::EXCLUDE_START())))->toBeTrue();
    expect($period(0, 2)->touchesWith($period(-2, 0, Boundaries::EXCLUDE_END())))->toBeTrue();
    expect($period(-2, -1)->touchesWith($period(0, 2, Boundaries::EXCLUDE_START())))->toBeFalse();
    expect($period(-2, -1)->touchesWith($period(0, 1)))->toBeTrue();
    expect($period(-1, 0)->touchesWith($period(1, 2)))->toBeTrue();

    expect($period(1, 2)->startsAfter($period(0, 2, Boundaries::EXCLUDE_START())->includedStart()))->toBeFalse();
    expect($period(-2, -1)->endsBefore($period(-2, 0, Boundaries::EXCLUDE_END())->includedEnd()))->toBeFalse();
})->with('days with a midnight change');

it('determines the overlap of periods around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    expect(midnightChangeDays($period(-2, 1)->overlap($period(0, 1, Boundaries::EXCLUDE_START()))))
        ->toBe(midnightChangeDays($period(1, 1)));

    expect(midnightChangeDays($period(-2, 0, Boundaries::EXCLUDE_END())->overlap($period(-1, 2))))
        ->toBe(midnightChangeDays($period(-1, -1)));

    expect($period(-2, 0)->overlap($period(0, 2, Boundaries::EXCLUDE_START())))->toBeNull();
})->with('days with a midnight change');

it('determines the gap between periods around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    $before = $period(-2, 0, Boundaries::EXCLUDE_END());
    $after = $period(0, 2, Boundaries::EXCLUDE_START());

    expect(midnightChangeDays($before->gap($after)))->toBe(midnightChangeDays($period(0, 0)));
    expect(midnightChangeDays($after->gap($before)))->toBe(midnightChangeDays($period(0, 0)));
    expect($period(-2, 0)->gap($after))->toBeNull();
    expect($before->gap($period(0, 2)))->toBeNull();
    expect($period(-2, 1)->gap($after))->toBeNull();
    expect($period(-1, 2)->gap($before))->toBeNull();

    expect(midnightChangeDays((new PeriodCollection($before, $after))->gaps()))->toBe(midnightChangeDays($period(0, 0)));
})->with('days with a midnight change');

it('subtracts periods around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    $week = $period(-3, 3);
    $expected = midnightChangeDays(new PeriodCollection($period(-3, -1), $period(1, 3)));

    expect(midnightChangeDays($week->subtract($period(0, 0))))->toBe($expected);
    expect(midnightChangeDays($week->subtract($period(-1, 1, Boundaries::EXCLUDE_ALL()))))->toBe($expected);
    expect(midnightChangeDays($week->subtract($period(-1, 0, Boundaries::EXCLUDE_START()))))->toBe($expected);
    expect(midnightChangeDays($week->subtract($period(0, 1, Boundaries::EXCLUDE_END()))))->toBe($expected);

    expect(midnightChangeDays($week->subtract($period(-3, 0, Boundaries::EXCLUDE_END()), $period(0, 3, Boundaries::EXCLUDE_START()))))
        ->toBe(midnightChangeDays($period(0, 0)));

    expect(midnightChangeDays($period(-2, 1)->subtract($period(0, 1, Boundaries::EXCLUDE_START()))))
        ->toBe(midnightChangeDays($period(-2, 0)));

    expect(midnightChangeDays($period(1, 2)->subtract($period(0, 1, Boundaries::EXCLUDE_START()))))
        ->toBe(midnightChangeDays($period(2, 2)));

    expect(midnightChangeDays($period(-1, 2)->subtract($period(-1, 0, Boundaries::EXCLUDE_END()))))
        ->toBe(midnightChangeDays($period(0, 2)));

    expect(midnightChangeDays($period(-2, -1)->subtract($period(-1, 0, Boundaries::EXCLUDE_END()))))
        ->toBe(midnightChangeDays($period(-2, -2)));
})->with('days with a midnight change');

it('determines the symmetric difference of periods around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    expect(midnightChangeDays($period(-2, 0)->diffSymmetric($period(0, 2, Boundaries::EXCLUDE_START()))))
        ->toBe(midnightChangeDays(new PeriodCollection($period(-2, 0), $period(1, 2))));

    expect(midnightChangeDays($period(-2, 1, Boundaries::EXCLUDE_END())->diffSymmetric($period(-1, 2))))
        ->toBe(midnightChangeDays(new PeriodCollection($period(-2, -2), $period(1, 2))));
})->with('days with a midnight change');

it('keeps the boundaries of a result around a midnight change', function (string $timezone, string $day) {
    $period = fn (int $from, int $to, ?Boundaries $boundaries = null) => midnightChangePeriod($timezone, $day, $from, $to, $boundaries);

    $overlap = $period(-2, 0, Boundaries::EXCLUDE_ALL())->overlap($period(-3, 3));

    expect($overlap->start()->getTimestamp())->toBe($period(-2, -2)->start()->getTimestamp());
    expect($overlap->end()->getTimestamp())->toBe($period(0, 0)->start()->getTimestamp());
    expect($overlap->equals($period(-1, -1)))->toBeTrue();

    $overlap = $period(0, 2, Boundaries::EXCLUDE_ALL())->overlap($period(-3, 3));

    expect($overlap->start()->getTimestamp())->toBe($period(0, 0)->start()->getTimestamp());
    expect($overlap->end()->getTimestamp())->toBe($period(2, 2)->start()->getTimestamp());
    expect($overlap->equals($period(1, 1)))->toBeTrue();
})->with('days with a midnight change');

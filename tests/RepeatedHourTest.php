<?php

use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

/*
 * When DST ends, one hour occurs twice. The dates below are given in UTC, so each one is
 * a single occurrence of that hour:
 *
 * Europe/Rome         2020-10-25  02:00 +02:00 is 00:00 UTC, 02:00 +01:00 is 01:00 UTC
 * America/New_York    2020-11-01  01:00 -04:00 is 05:00 UTC, 01:00 -05:00 is 06:00 UTC
 * Australia/Lord_Howe 2020-04-05  01:30 +11:00 is 14:30 UTC the day before, 01:30 +10:30 is 15:00 UTC
 *
 * Other zones repeat midnight (America/Havana, Atlantic/Azores), the last hour of the day
 * (Asia/Beirut, America/Santiago), two hours (Antarctica/Troll), or an hour that does not start
 * at minute zero in UTC (Pacific/Chatham). Europe/Moscow went back one hour in 2014 and
 * America/Caracas half an hour in 2007, both for a permanent change that is not DST.
 */
function repeatedHourDate(string $utc, string $timezone): DateTimeImmutable
{
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($timezone));
}

function repeatedHourFormat(DateTimeInterface $date): string
{
    return $date->format('Y-m-d H:i:s P');
}

function repeatedHourPeriod(string $utcStart, string $utcEnd, ?Boundaries $boundaries = null, string $timezone = 'Europe/Rome'): Period
{
    return Period::make(
        repeatedHourDate($utcStart, $timezone),
        repeatedHourDate($utcEnd, $timezone),
        Precision::HOUR(),
        $boundaries,
    );
}

it('rounds a date in a repeated hour to the same occurrence', function (string $timezone, string $utc, Precision $precision, string $expected) {
    $rounded = $precision->roundDate(repeatedHourDate($utc, $timezone));

    expect(repeatedHourFormat($rounded))->toBe($expected);
})->with([
    'Rome, first, second' => ['Europe/Rome', '2020-10-25 00:15:30', Precision::SECOND(), '2020-10-25 02:15:30 +02:00'],
    'Rome, first, minute' => ['Europe/Rome', '2020-10-25 00:15:30', Precision::MINUTE(), '2020-10-25 02:15:00 +02:00'],
    'Rome, first, hour' => ['Europe/Rome', '2020-10-25 00:15:30', Precision::HOUR(), '2020-10-25 02:00:00 +02:00'],
    'Rome, second, second' => ['Europe/Rome', '2020-10-25 01:15:30', Precision::SECOND(), '2020-10-25 02:15:30 +01:00'],
    'Rome, second, minute' => ['Europe/Rome', '2020-10-25 01:15:30', Precision::MINUTE(), '2020-10-25 02:15:00 +01:00'],
    'Rome, second, hour' => ['Europe/Rome', '2020-10-25 01:15:30', Precision::HOUR(), '2020-10-25 02:00:00 +01:00'],
    'New York, first, second' => ['America/New_York', '2020-11-01 05:15:30', Precision::SECOND(), '2020-11-01 01:15:30 -04:00'],
    'New York, first, minute' => ['America/New_York', '2020-11-01 05:15:30', Precision::MINUTE(), '2020-11-01 01:15:00 -04:00'],
    'New York, first, hour' => ['America/New_York', '2020-11-01 05:15:30', Precision::HOUR(), '2020-11-01 01:00:00 -04:00'],
    'New York, second, second' => ['America/New_York', '2020-11-01 06:15:30', Precision::SECOND(), '2020-11-01 01:15:30 -05:00'],
    'New York, second, minute' => ['America/New_York', '2020-11-01 06:15:30', Precision::MINUTE(), '2020-11-01 01:15:00 -05:00'],
    'New York, second, hour' => ['America/New_York', '2020-11-01 06:15:30', Precision::HOUR(), '2020-11-01 01:00:00 -05:00'],
    'Lord Howe, first, second' => ['Australia/Lord_Howe', '2020-04-04 14:45:30', Precision::SECOND(), '2020-04-05 01:45:30 +11:00'],
    'Lord Howe, first, minute' => ['Australia/Lord_Howe', '2020-04-04 14:45:30', Precision::MINUTE(), '2020-04-05 01:45:00 +11:00'],
    'Lord Howe, second, second' => ['Australia/Lord_Howe', '2020-04-04 15:15:30', Precision::SECOND(), '2020-04-05 01:45:30 +10:30'],
    'Lord Howe, second, minute' => ['Australia/Lord_Howe', '2020-04-04 15:15:30', Precision::MINUTE(), '2020-04-05 01:45:00 +10:30'],
    'Havana, first, hour' => ['America/Havana', '2020-11-01 04:30:00', Precision::HOUR(), '2020-11-01 00:00:00 -04:00'],
    'Havana, second, hour' => ['America/Havana', '2020-11-01 05:30:00', Precision::HOUR(), '2020-11-01 00:00:00 -05:00'],
    'Azores, first, hour' => ['Atlantic/Azores', '2020-10-25 00:30:00', Precision::HOUR(), '2020-10-25 00:00:00 +00:00'],
    'Azores, second, hour' => ['Atlantic/Azores', '2020-10-25 01:30:00', Precision::HOUR(), '2020-10-25 00:00:00 -01:00'],
    'Beirut, first, hour' => ['Asia/Beirut', '2020-10-24 20:30:00', Precision::HOUR(), '2020-10-24 23:00:00 +03:00'],
    'Beirut, second, hour' => ['Asia/Beirut', '2020-10-24 21:30:00', Precision::HOUR(), '2020-10-24 23:00:00 +02:00'],
    'Santiago, first, hour' => ['America/Santiago', '2020-04-05 02:30:00', Precision::HOUR(), '2020-04-04 23:00:00 -03:00'],
    'Santiago, second, hour' => ['America/Santiago', '2020-04-05 03:30:00', Precision::HOUR(), '2020-04-04 23:00:00 -04:00'],
    'Troll, first, hour' => ['Antarctica/Troll', '2020-10-25 00:30:00', Precision::HOUR(), '2020-10-25 02:00:00 +02:00'],
    'Troll, second, hour' => ['Antarctica/Troll', '2020-10-25 02:30:00', Precision::HOUR(), '2020-10-25 02:00:00 +00:00'],
    'Chatham, first, hour' => ['Pacific/Chatham', '2020-04-04 13:30:00', Precision::HOUR(), '2020-04-05 03:00:00 +13:45'],
    'Chatham, second, hour' => ['Pacific/Chatham', '2020-04-04 14:30:00', Precision::HOUR(), '2020-04-05 03:00:00 +12:45'],
    'Moscow, first, hour' => ['Europe/Moscow', '2014-10-25 21:30:00', Precision::HOUR(), '2014-10-26 01:00:00 +04:00'],
    'Moscow, second, hour' => ['Europe/Moscow', '2014-10-25 22:30:00', Precision::HOUR(), '2014-10-26 01:00:00 +03:00'],
    'Caracas, first, minute' => ['America/Caracas', '2007-12-09 06:45:00', Precision::MINUTE(), '2007-12-09 02:45:00 -04:00'],
    'Caracas, second, minute' => ['America/Caracas', '2007-12-09 07:15:00', Precision::MINUTE(), '2007-12-09 02:45:00 -04:30'],
]);

it('rounds every date of a day to the same start when midnight is repeated', function (string $timezone, string $expected) {
    $dates = [
        'first' => repeatedHourDate('2020-11-01 04:30:00', $timezone),
        'second' => repeatedHourDate('2020-11-01 05:30:00', $timezone),
        'later' => repeatedHourDate('2020-11-01 17:00:00', $timezone),
    ];

    foreach ([Precision::DAY(), Precision::MONTH()] as $precision) {
        foreach ($dates as $date) {
            expect(repeatedHourFormat($precision->roundDate($date)))->toBe($expected);
        }
    }

    $first = Period::make($dates['first'], $dates['first']);
    $second = Period::make($dates['second'], $dates['second']);
    $later = Period::make($dates['later'], $dates['later']);

    expect($first->equals($second))->toBeTrue();
    expect($first->equals($later))->toBeTrue();
    expect($first->length())->toBe(1);
})->with([
    // 01:00 -04:00 goes back to 00:00 -05:00
    'Havana' => ['America/Havana', '2020-11-01 00:00:00 -04:00'],
]);

it('ceils every date of a day to the same end when its last hour is repeated', function () {
    // 24:00 +03:00 goes back to 23:00 +02:00
    $dates = [
        repeatedHourDate('2020-10-24 09:00:00', 'Asia/Beirut'),
        repeatedHourDate('2020-10-24 20:30:00', 'Asia/Beirut'),
        repeatedHourDate('2020-10-24 21:30:00', 'Asia/Beirut'),
    ];

    foreach ($dates as $date) {
        $ceiled = Precision::SECOND()->ceilDate($date, Precision::DAY());

        expect(repeatedHourFormat($ceiled))->toBe('2020-10-24 23:59:59 +02:00');
    }
});

it('increments and decrements by one hour through a repeated time', function (string $timezone, string $utc) {
    $precision = Precision::HOUR();
    $start = $precision->roundDate(repeatedHourDate($utc, $timezone));
    $date = $start;

    for ($step = 0; $step < 7; $step++) {
        $next = $precision->increment($date);

        expect($next->getTimestamp() - $date->getTimestamp())->toBe(3600);

        $date = $next;
    }

    for ($step = 0; $step < 7; $step++) {
        $previous = $precision->decrement($date);

        expect($date->getTimestamp() - $previous->getTimestamp())->toBe(3600);

        $date = $previous;
    }

    expect($date)->toEqual($start);
})->with([
    'Rome' => ['Europe/Rome', '2020-10-24 21:00:00'],
    'New York' => ['America/New_York', '2020-11-01 02:00:00'],
    'Havana' => ['America/Havana', '2020-11-01 01:00:00'],
    'Azores' => ['Atlantic/Azores', '2020-10-24 21:00:00'],
    'Beirut' => ['Asia/Beirut', '2020-10-24 17:00:00'],
    'Santiago' => ['America/Santiago', '2020-04-04 23:00:00'],
    'Troll' => ['Antarctica/Troll', '2020-10-24 21:00:00'],
    'Chatham' => ['Pacific/Chatham', '2020-04-04 10:15:00'],
    'Moscow' => ['Europe/Moscow', '2014-10-25 18:00:00'],
]);

it('counts both occurrences in the length of a period', function (string $timezone, string $utcStart, string $utcEnd, int $length) {
    $period = Period::make(
        repeatedHourDate($utcStart, $timezone),
        repeatedHourDate($utcEnd, $timezone),
        Precision::HOUR(),
    );

    expect($period->length())->toBe($length);
})->with([
    'Rome' => ['Europe/Rome', '2020-10-25 00:00:00', '2020-10-25 01:00:00', 2],
    'Havana' => ['America/Havana', '2020-11-01 04:00:00', '2020-11-01 05:00:00', 2],
    'Troll, two repeated hours' => ['Antarctica/Troll', '2020-10-24 23:00:00', '2020-10-25 02:00:00', 4],
    'Moscow' => ['Europe/Moscow', '2014-10-25 21:00:00', '2014-10-25 22:00:00', 2],
]);

it('rounds to a time that is not repeated from either occurrence', function (string $timezone, string $utc, Precision $precision, string $expected) {
    $rounded = $precision->roundDate(repeatedHourDate($utc, $timezone));

    expect(repeatedHourFormat($rounded))->toBe($expected);
})->with([
    'Rome, first, day' => ['Europe/Rome', '2020-10-25 00:15:30', Precision::DAY(), '2020-10-25 00:00:00 +02:00'],
    'Rome, second, day' => ['Europe/Rome', '2020-10-25 01:15:30', Precision::DAY(), '2020-10-25 00:00:00 +02:00'],
    'Rome, first, month' => ['Europe/Rome', '2020-10-25 00:15:30', Precision::MONTH(), '2020-10-01 00:00:00 +02:00'],
    'Rome, second, month' => ['Europe/Rome', '2020-10-25 01:15:30', Precision::MONTH(), '2020-10-01 00:00:00 +02:00'],
    'Rome, first, year' => ['Europe/Rome', '2020-10-25 00:15:30', Precision::YEAR(), '2020-01-01 00:00:00 +01:00'],
    'Rome, second, year' => ['Europe/Rome', '2020-10-25 01:15:30', Precision::YEAR(), '2020-01-01 00:00:00 +01:00'],
    'New York, first, day' => ['America/New_York', '2020-11-01 05:15:30', Precision::DAY(), '2020-11-01 00:00:00 -04:00'],
    'New York, second, day' => ['America/New_York', '2020-11-01 06:15:30', Precision::DAY(), '2020-11-01 00:00:00 -04:00'],
    // Only the half hour from 01:30 is repeated, so 01:00 exists once
    'Lord Howe, first, hour' => ['Australia/Lord_Howe', '2020-04-04 14:45:30', Precision::HOUR(), '2020-04-05 01:00:00 +11:00'],
    'Lord Howe, second, hour' => ['Australia/Lord_Howe', '2020-04-04 15:15:30', Precision::HOUR(), '2020-04-05 01:00:00 +11:00'],
]);

it('rounds a mutable date in a repeated hour to the same occurrence', function () {
    $date = new DateTime('2020-10-25 00:15:30', new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone('Europe/Rome'));

    expect(repeatedHourFormat(Precision::HOUR()->roundDate($date)))->toBe('2020-10-25 02:00:00 +02:00');
});

it('ceils a date in a repeated hour to the same occurrence', function (string $utc, Precision $precision, string $expected) {
    $ceiled = Precision::SECOND()->ceilDate(repeatedHourDate($utc, 'Europe/Rome'), $precision);

    expect(repeatedHourFormat($ceiled))->toBe($expected);
})->with([
    'first, minute' => ['2020-10-25 00:15:30', Precision::MINUTE(), '2020-10-25 02:15:59 +02:00'],
    'first, hour' => ['2020-10-25 00:15:30', Precision::HOUR(), '2020-10-25 02:59:59 +02:00'],
    'first, day' => ['2020-10-25 00:15:30', Precision::DAY(), '2020-10-25 23:59:59 +01:00'],
    'second, minute' => ['2020-10-25 01:15:30', Precision::MINUTE(), '2020-10-25 02:15:59 +01:00'],
    'second, hour' => ['2020-10-25 01:15:30', Precision::HOUR(), '2020-10-25 02:59:59 +01:00'],
    'second, day' => ['2020-10-25 01:15:30', Precision::DAY(), '2020-10-25 23:59:59 +01:00'],
]);

it('determines the ceiling end of a period that ends in the first occurrence', function () {
    $period = Period::make(
        repeatedHourDate('2020-10-24 23:00:00', 'Europe/Rome'),
        repeatedHourDate('2020-10-25 00:15:00', 'Europe/Rome'),
        Precision::MINUTE(),
    );

    expect(repeatedHourFormat($period->ceilingEnd(Precision::HOUR())))->toBe('2020-10-25 02:59:59 +02:00');
});

it('increments and decrements through both occurrences of a repeated hour', function () {
    $precision = Precision::HOUR();
    $date = repeatedHourDate('2020-10-24 23:00:00', 'Europe/Rome');
    $forward = [];
    $backward = [];

    for ($step = 0; $step < 3; $step++) {
        $date = $precision->increment($date);
        $forward[] = repeatedHourFormat($date);
    }

    for ($step = 0; $step < 3; $step++) {
        $date = $precision->decrement($date);
        $backward[] = repeatedHourFormat($date);
    }

    expect($forward)->toBe(['2020-10-25 02:00:00 +02:00', '2020-10-25 02:00:00 +01:00', '2020-10-25 03:00:00 +01:00']);
    expect($backward)->toBe(['2020-10-25 02:00:00 +01:00', '2020-10-25 02:00:00 +02:00', '2020-10-25 01:00:00 +02:00']);
});

it('still skips the hour that does not exist when DST starts', function () {
    $precision = Precision::HOUR();
    $timezone = new DateTimeZone('Europe/Rome');

    $rounded = $precision->roundDate(new DateTimeImmutable('2020-03-29 03:30:00', $timezone));
    $incremented = $precision->increment(new DateTimeImmutable('2020-03-29 01:00:00', $timezone));
    $decremented = $precision->decrement(new DateTimeImmutable('2020-03-29 03:00:00', $timezone));

    expect(repeatedHourFormat($rounded))->toBe('2020-03-29 03:00:00 +02:00');
    expect(repeatedHourFormat($incremented))->toBe('2020-03-29 03:00:00 +02:00');
    expect(repeatedHourFormat($decremented))->toBe('2020-03-29 01:00:00 +01:00');
});

it('keeps the occurrence of the dates a period is made with', function () {
    $period = repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 01:00:00');

    expect(repeatedHourFormat($period->start()))->toBe('2020-10-25 02:00:00 +02:00');
    expect(repeatedHourFormat($period->end()))->toBe('2020-10-25 02:00:00 +01:00');
    expect($period->length())->toBe(2);
});

it('determines the included dates of a period with excluded boundaries around a repeated hour', function () {
    $period = repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-25 02:00:00', Boundaries::EXCLUDE_ALL());

    expect(repeatedHourFormat($period->start()))->toBe('2020-10-25 01:00:00 +02:00');
    expect(repeatedHourFormat($period->includedStart()))->toBe('2020-10-25 02:00:00 +02:00');
    expect(repeatedHourFormat($period->includedEnd()))->toBe('2020-10-25 02:00:00 +01:00');
    expect(repeatedHourFormat($period->end()))->toBe('2020-10-25 03:00:00 +01:00');
    expect($period->length())->toBe(2);
});

it('counts every minute of a period that spans both occurrences', function () {
    $period = Period::make(
        repeatedHourDate('2020-10-25 00:15:00', 'Europe/Rome'),
        repeatedHourDate('2020-10-25 01:15:00', 'Europe/Rome'),
        Precision::MINUTE(),
    );

    expect($period->length())->toBe(61);
});

it('compares the two occurrences of a repeated hour as different periods', function () {
    $first = repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 00:00:00');
    $second = repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 01:00:00');

    expect($first->equals($second))->toBeFalse();
    expect($first->overlapsWith($second))->toBeFalse();
    expect($first->touchesWith($second))->toBeTrue();
    expect($first->gap($second))->toBeNull();
    expect($first->renew()->equals($second))->toBeTrue();
});

it('compares a date with the occurrence a period is in', function () {
    $first = repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 00:00:00');
    $second = repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 01:00:00');

    $inFirst = repeatedHourDate('2020-10-25 00:30:00', 'Europe/Rome');
    $inSecond = repeatedHourDate('2020-10-25 01:30:00', 'Europe/Rome');

    expect($first->contains($inFirst))->toBeTrue();
    expect($first->contains($inSecond))->toBeFalse();
    expect($second->contains($inFirst))->toBeFalse();
    expect($second->contains($inSecond))->toBeTrue();

    expect($first->startsAt($inFirst))->toBeTrue();
    expect($first->startsAt($inSecond))->toBeFalse();
    expect($second->endsAt($inSecond))->toBeTrue();
    expect($second->endsAt($inFirst))->toBeFalse();
    expect($second->startsAfter($inFirst))->toBeTrue();
    expect($first->endsBefore($inSecond))->toBeTrue();
});

it('determines the gap between periods around a repeated hour', function () {
    $before = repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-24 23:00:00');
    $after = repeatedHourPeriod('2020-10-25 02:00:00', '2020-10-25 02:00:00');

    $gap = $before->gap($after);

    expect(repeatedHourFormat($gap->includedStart()))->toBe('2020-10-25 02:00:00 +02:00');
    expect(repeatedHourFormat($gap->includedEnd()))->toBe('2020-10-25 02:00:00 +01:00');
});

it('overlaps one occurrence of a repeated hour', function () {
    $whole = repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-25 02:00:00');
    $first = repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 00:00:00');
    $second = repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 01:00:00');

    expect($whole->overlap($first)->equals($first))->toBeTrue();
    expect($whole->overlap($second)->equals($second))->toBeTrue();
    expect($first->overlap($second))->toBeNull();
});

it('subtracts one occurrence of a repeated hour', function () {
    $whole = repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-25 02:00:00');
    $first = repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 00:00:00');
    $second = repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 01:00:00');

    $withoutFirst = $whole->subtract($first);

    expect($withoutFirst)->toHaveCount(2);
    expect($withoutFirst[0]->equals(repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-24 23:00:00')))->toBeTrue();
    expect($withoutFirst[1]->equals(repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 02:00:00')))->toBeTrue();

    $withoutSecond = $whole->subtract($second);

    expect($withoutSecond)->toHaveCount(2);
    expect($withoutSecond[0]->equals(repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-25 00:00:00')))->toBeTrue();
    expect($withoutSecond[1]->equals(repeatedHourPeriod('2020-10-25 02:00:00', '2020-10-25 02:00:00')))->toBeTrue();
});

it('subtracts both occurrences of a repeated hour', function () {
    $whole = repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-25 02:00:00');
    $first = repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 00:00:00');
    $second = repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 01:00:00');

    $remaining = $whole->subtract($first, $second);

    expect($remaining)->toHaveCount(2);
    expect($remaining[0]->equals(repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-24 23:00:00')))->toBeTrue();
    expect($remaining[1]->equals(repeatedHourPeriod('2020-10-25 02:00:00', '2020-10-25 02:00:00')))->toBeTrue();
});

it('keeps a period inside a repeated hour when the subtracted periods do not overlap it', function () {
    $period = repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-25 02:00:00', Boundaries::EXCLUDE_ALL());

    $others = new PeriodCollection(
        repeatedHourPeriod('2020-10-24 11:00:00', '2020-10-24 12:00:00', Boundaries::EXCLUDE_END()),
        repeatedHourPeriod('2020-10-25 06:00:00', '2020-10-25 09:00:00', Boundaries::EXCLUDE_START()),
    );

    $remaining = (new PeriodCollection($period))->subtract($others);

    expect($remaining)->toHaveCount(1);
    expect($remaining[0]->equals($period))->toBeTrue();
});

it('does not return a subtracted hour when the subtracted period ends in a repeated hour', function () {
    $timezone = 'America/New_York';

    $period = repeatedHourPeriod('2020-11-01 00:00:00', '2020-11-01 09:00:00', Boundaries::EXCLUDE_ALL(), $timezone);
    $endsInSecondOccurrence = repeatedHourPeriod('2020-10-31 23:00:00', '2020-11-01 07:00:00', Boundaries::EXCLUDE_ALL(), $timezone);
    $before = repeatedHourPeriod('2020-10-31 19:00:00', '2020-10-31 22:00:00', Boundaries::EXCLUDE_ALL(), $timezone);

    $expected = repeatedHourPeriod('2020-11-01 07:00:00', '2020-11-01 08:00:00', null, $timezone);

    $single = $period->subtract($endsInSecondOccurrence);

    expect($single)->toHaveCount(1);
    expect($single[0]->includedStart())->toEqual($expected->includedStart());
    expect($single[0]->includedEnd())->toEqual($expected->includedEnd());

    $multiple = $period->subtract($endsInSecondOccurrence, $before);

    expect($multiple)->toHaveCount(1);
    expect($multiple[0]->includedStart())->toEqual($expected->includedStart());
    expect($multiple[0]->includedEnd())->toEqual($expected->includedEnd());
});

it('determines the gaps of a collection around a repeated hour', function () {
    $collection = new PeriodCollection(
        repeatedHourPeriod('2020-10-24 23:00:00', '2020-10-24 23:00:00'),
        repeatedHourPeriod('2020-10-25 01:00:00', '2020-10-25 01:00:00'),
    );

    $gaps = $collection->gaps();

    expect($gaps)->toHaveCount(1);
    expect($gaps[0]->equals(repeatedHourPeriod('2020-10-25 00:00:00', '2020-10-25 00:00:00')))->toBeTrue();
});

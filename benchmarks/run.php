<?php

/*
 * Usage: XDEBUG_MODE=off php benchmarks/run.php [path/to/src]
 *
 * The optional src path makes it possible to run the same scenarios against
 * another checkout (e.g. a git worktree of main) to compare results.
 */

$src = rtrim($argv[1] ?? __DIR__ . '/../src', '/');

spl_autoload_register(function (string $class) use ($src) {
    $prefix = 'Spatie\\Period\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $file = $src . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

function periods(int $count, int $seed = 42): array
{
    mt_srand($seed);

    $base = new DateTimeImmutable('2000-01-01');
    $periods = [];
    $offset = 0;

    for ($i = 0; $i < $count; $i++) {
        $offset += mt_rand(1, 10);
        $start = $base->modify("+{$offset} days");
        $periods[] = Period::make($start, $start->modify('+' . mt_rand(0, 8) . ' days'));
    }

    return $periods;
}

function collection(int $count, int $seed = 42): PeriodCollection
{
    return new PeriodCollection(...periods($count, $seed));
}

function bench(string $name, int $iterations, Closure $setup, Closure $run): void
{
    $input = $setup();
    $run($input);

    $times = [];

    for ($repetition = 0; $repetition < 5; $repetition++) {
        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $run($input);
        }

        $times[] = (hrtime(true) - $start) / $iterations / 1e6;
    }

    sort($times);

    printf("%-40s %12.3f %12.3f\n", $name, $times[0], $times[2]);
}

printf("%-40s %12s %12s\n", 'scenario', 'min (ms)', 'median (ms)');

bench('Period::make x1000', 20, fn () => null, function () {
    for ($i = 0; $i < 1000; $i++) {
        Period::make('2020-01-01', '2020-12-31');
    }
});

bench('Period::length MONTH 100y x100', 20, fn () => Period::make('1900-01-01', '1999-12-01', Precision::MONTH()), function (Period $period) {
    for ($i = 0; $i < 100; $i++) {
        $period->length();
    }
});

bench('Period::length YEAR 1000y x100', 20, fn () => Period::make('1000-01-01', '1999-01-01', Precision::YEAR()), function (Period $period) {
    for ($i = 0; $i < 100; $i++) {
        $period->length();
    }
});

bench('Period::touchesWith pairs n=200', 20, fn () => periods(200), function (array $periods) {
    foreach ($periods as $a) {
        $a->touchesWith($periods[0]);
    }

    for ($i = 1; $i < count($periods); $i++) {
        $periods[$i - 1]->touchesWith($periods[$i]);
    }
});

bench('Period::subtract(...n) n=200', 5, fn () => [Period::make('2000-01-01', '2010-12-31'), periods(200)], function (array $input) {
    $input[0]->subtract(...$input[1]);
});

foreach ([50, 200, 1000] as $n) {
    $iterations = max(1, intdiv(1000, $n));

    bench("PeriodCollection::boundaries n={$n}", $iterations * 10, fn () => collection($n), fn (PeriodCollection $c) => $c->boundaries());

    bench("PeriodCollection::sort n={$n}", $iterations * 10, function () use ($n) {
        $periods = periods($n);
        shuffle($periods);

        return new PeriodCollection(...$periods);
    }, fn (PeriodCollection $c) => $c->sort());

    bench("PeriodCollection::intersect n={$n}", $iterations * 10, fn () => collection($n), fn (PeriodCollection $c) => $c->intersect(Period::make('2001-01-01', '2005-12-31')));

    bench("PeriodCollection::overlapAll n={$n}", $iterations, fn () => [collection($n, 1), collection($n, 2)], fn (array $c) => $c[0]->overlapAll($c[1]));

    bench("PeriodCollection::subtract n={$n}", $iterations, fn () => [collection($n, 1), collection(intdiv($n, 10), 2)], fn (array $c) => $c[0]->subtract($c[1]));

    bench("PeriodCollection::gaps n={$n}", $iterations, fn () => collection($n), fn (PeriodCollection $c) => $c->gaps());

    bench("PeriodCollection::union n={$n}", $iterations, fn () => collection($n), fn (PeriodCollection $c) => $c->union());
}

printf("\npeak memory: %.1f MB\n", memory_get_peak_usage(true) / 1048576);

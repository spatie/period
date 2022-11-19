<?php

use Spatie\Period\Period;
use Spatie\Period\Precision;

it('can determine if two periods touch each other', function () {
    expect(Period::make('2018-01-01', '2018-01-01')->touchesWith(Period::make('2018-01-02', '2018-01-02')))->toBeTrue();

    expect(Period::make('2018-01-02', '2018-01-02')->touchesWith(Period::make('2018-01-01', '2018-01-01')))->toBeTrue();

    expect(Period::make('2018-01-01', '2018-01-01')->touchesWith(Period::make('2018-01-03', '2018-01-03')))->toBeFalse();

    expect(Period::make('2018-01-03', '2018-01-03')->touchesWith(Period::make('2018-01-01', '2018-01-01')))->toBeFalse();

    expect(Period::make('2018-01-01 06:30:00', '2018-01-01 07:30:00', Precision::HOUR())->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::HOUR())))->toBeFalse();

    expect(Period::make('2018-01-01 06:30:00', '2018-01-01 08:30:00', Precision::HOUR())->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::HOUR())))->toBeTrue();

    expect(Period::make('2018-01-01 06:30:00', '2018-01-01 07:30:00', Precision::SECOND())->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::SECOND())))->toBeFalse();
});

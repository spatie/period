<?php

use Spatie\Period\Period;
use Spatie\Period\Precision;

it('can determine if two periods touch each other', function () {
    $this->assertTrue(
        Period::make('2018-01-01', '2018-01-01')
        ->touchesWith(Period::make('2018-01-02', '2018-01-02'))
    );

    $this->assertTrue(
        Period::make('2018-01-02', '2018-01-02')
        ->touchesWith(Period::make('2018-01-01', '2018-01-01'))
    );

    $this->assertFalse(
        Period::make('2018-01-01', '2018-01-01')
        ->touchesWith(Period::make('2018-01-03', '2018-01-03'))
    );

    $this->assertFalse(
        Period::make('2018-01-03', '2018-01-03')
        ->touchesWith(Period::make('2018-01-01', '2018-01-01'))
    );

    $this->assertFalse(
        Period::make('2018-01-01 06:30:00', '2018-01-01 07:30:00', Precision::HOUR())
        ->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::HOUR()))
    );

    $this->assertTrue(
        Period::make('2018-01-01 06:30:00', '2018-01-01 08:30:00', Precision::HOUR())
        ->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::HOUR()))
    );

    $this->assertFalse(
        Period::make('2018-01-01 06:30:00', '2018-01-01 07:30:00', Precision::SECOND())
        ->touchesWith(Period::make('2018-01-01 09:00:00', '2018-01-01 10:00:00', Precision::SECOND()))
    );
});

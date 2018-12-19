<?php

namespace Spatie\Period\Tests;

use Spatie\Period\Period;
use Spatie\Period\Visualizer;
use PHPUnit\Framework\TestCase;
use Spatie\Period\PeriodCollection;

class VisualizerTest extends TestCase
{
    /**
     * @test
     * @dataProvider overlapping
     * @dataProvider nonOverlapping
     * @dataProvider singleOverlaps
     * @dataProvider multipleOverlaps
     */
    public function it_can_visualize_periods($options, $expected, $blocks)
    {
        $visualizer = new Visualizer($options);

        $actual = $visualizer->visualize($blocks);
        $actual = explode("\n", $actual);

        $this->assertEquals($expected, $actual);
    }

    public function overlapping(): array
    {
        return [
            [
                ['width' => 15],
                [
                    'A    [=========]    ',
                    'B        [=========]',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-02-01'),
                    'B' => Period::make('2018-01-15', '2018-02-15'),
                ],
            ],

            [
                ['width' => 15],
                [
                    'A           []      ',
                    'B    [=============]',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-02-01'),
                    'B' => Period::make('2017-01-01', '2019-01-01'),
                ],
            ],

            [
                ['width' => 15],
                [
                    'A           [======]',
                    'B    [=========]    ',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-02-01'),
                    'B' => Period::make('2017-12-01', '2018-01-15'),
                ],
            ],

            [
                ['width' => 15],
                [
                    'A    [=============]',
                    'B           []      ',
                ],
                [
                    'A' => Period::make('2017-01-01', '2019-01-01'),
                    'B' => Period::make('2018-01-01', '2018-02-01'),
                ],
            ],

            [
                ['width' => 15],
                [
                    'A    [=============]',
                    'B    [=============]',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-02-01'),
                    'B' => Period::make('2018-01-01', '2018-02-01'),
                ],
            ],
        ];
    }

    public function nonOverlapping(): array
    {
        return [
            [
                ['width' => 15],
                [
                    'A    [======]       ',
                    'B            [=====]',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-01-31'),
                    'B' => Period::make('2018-02-01', '2018-02-28'),
                ],
            ],

            [
                ['width' => 15],
                [
                    'A            [=====]',
                    'B    [======]       ',
                ],
                [
                    'A' => Period::make('2018-02-01', '2018-02-28'),
                    'B' => Period::make('2018-01-01', '2018-01-31'),
                ],
            ],
        ];
    }

    public function singleOverlaps(): array
    {
        return [
            [
                ['width' => 15],
                [
                    'A          [======]       ',
                    'B              [=========]',
                    'OVERLAP        [==]       ',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-01-15'),
                    'B' => Period::make('2018-01-10', '2018-01-30'),
                    'OVERLAP' => Period::make('2018-01-10', '2018-01-15'),
                ],
            ],
        ];
    }

    public function multipleOverlaps(): array
    {
        return [
            [
                ['width' => 27],
                [
                    'A          [========]                 ',
                    'B                      [==]           ',
                    'C                           [========]',
                    'D               [==============]      ',
                    'OVERLAP         [===]  [==] [==]      ',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-01-31'),
                    'B' => Period::make('2018-02-10', '2018-02-20'),
                    'C' => Period::make('2018-03-01', '2018-03-31'),
                    'D' => Period::make('2018-01-20', '2018-03-10'),
                    'OVERLAP' => new PeriodCollection(
                        Period::make('2018-01-20', '2018-01-31'),
                        Period::make('2018-02-10', '2018-02-20'),
                        Period::make('2018-03-01', '2018-03-10')
                    ),
                ],
            ],
            [
                ['width' => 27],
                [
                    'A          [========]                 ',
                    'B                      [==]           ',
                    'C                           [========]',
                    'OVERLAP      [=]                      ',
                ],
                [
                    'A' => Period::make('2018-01-01', '2018-01-31'),
                    'B' => Period::make('2018-02-10', '2018-02-20'),
                    'C' => Period::make('2018-03-01', '2018-03-31'),
                    'OVERLAP' => Period::make('2018-01-10', '2018-01-15'),
                ],
            ],
        ];
    }
}

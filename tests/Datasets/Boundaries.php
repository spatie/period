<?php

dataset('boundaries_for_subtract', function () {
    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [=========)
        'subtract' => '[2021-01-15,2021-02-01]',    //                  [=====]
        'result' => '[2021-01-01,2021-01-15)',    //          [=======)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [=========)
        'subtract' => '(2021-01-15,2021-02-01]',    //                  (=====]
        'result' => '[2021-01-01,2021-01-16)',    //          [========)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01]',    //          [=========]
        'subtract' => '(2021-01-15,2021-02-01]',    //                  (=====]
        'result' => '[2021-01-01,2021-01-15]',    //          [=======]
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01]',    //          [=========]
        'subtract' => '[2021-01-15,2021-02-01]',    //                  [=====]
        'result' => '[2021-01-01,2021-01-14]',    //          [======]
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01]',    //                  [=======]
        'subtract' => '[2021-01-01,2021-01-10]',    //          [=========]
        'result' => '[2021-01-11,2021-02-01]',    //                     [====]
    ];

    yield [
        'period' => '(2021-01-01,2021-02-01]',    //                  (=======]
        'subtract' => '[2021-01-01,2021-01-10]',    //          [=========]
        'result' => '(2021-01-10,2021-02-01]',    //                    (=====]
    ];

    yield [
        'period' => '(2021-01-01,2021-02-01]',    //                  (=======]
        'subtract' => '[2021-01-01,2021-01-10)',    //          [=========)
        'result' => '(2021-01-09,2021-02-01]',    //                   (======]
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01]',    //                  [=======]
        'subtract' => '[2021-01-01,2021-01-10)',    //          [=========)
        'result' => '[2021-01-10,2021-02-01]',    //                    [=====]
    ];
});

dataset('boundaries_for_overlap', function () {
    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [==================)
        'overlap' => '[2021-01-10,2021-01-15]',    //               [=========]
        'result' => '[2021-01-10,2021-01-16)',    //               [==========)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [==================)
        'overlap' => '[2021-01-10,2021-01-15)',    //               [=========)
        'result' => '[2021-01-10,2021-01-15)',    //               [=========)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [==================)
        'overlap' => '[2021-01-10,2021-02-15)',    //                      [=========)
        'result' => '[2021-01-10,2021-02-01)',    //                      [======)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [==================)
        'overlap' => '[2021-01-10,2021-02-15]',    //                      [=========]
        'result' => '[2021-01-10,2021-02-01)',    //                      [======)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [==================)
        'overlap' => '[2021-01-01,2021-01-15]',    //     [===========]
        'result' => '[2021-01-01,2021-01-16)',    //          [=======)
    ];

    yield [
        'period' => '[2021-01-01,2021-02-01)',    //          [==================)
        'overlap' => '[2021-01-01,2021-01-15)',    //     [===========)
        'result' => '[2021-01-01,2021-01-15)',    //          [======)
    ];

    yield [
        'period' => '(2021-01-01,2021-02-01)',    //          (==================)
        'overlap' => '[2021-01-10,2021-01-15]',    //                   [======]
        'result' => '(2021-01-09,2021-01-16)',    //                  (========)
    ];

    yield [
        'period' => '(2021-01-01,2021-02-01)',    //          (==================)
        'overlap' => '(2021-01-10,2021-01-15)',    //                   (======)
        'result' => '(2021-01-10,2021-01-15)',    //                   (======)
    ];
});

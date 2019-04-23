<?php

namespace Spatie\Period;

interface Precision
{
    public const YEAR = 0b100000;
    public const MONTH = 0b110000;
    public const DAY = 0b111000;
    public const HOUR = 0b111100;
    public const MINUTE = 0b111110;
    public const SECOND = 0b111111;
}

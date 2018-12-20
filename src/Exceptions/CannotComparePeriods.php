<?php

namespace Spatie\Period\Exceptions;

use Exception;

class CannotComparePeriods extends Exception
{
    public static function precisionDoesNotMatch(): CannotComparePeriods
    {
        return new self("Cannot compare two periods whose precision doesn't match.");
    }
}

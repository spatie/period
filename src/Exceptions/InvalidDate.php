<?php

namespace Spatie\Period\Exceptions;

use InvalidArgumentException;

class InvalidDate extends InvalidArgumentException
{
    public static function cannotBeNull(string $parameter): InvalidDate
    {
        return new static("{$parameter} cannot be null");
    }

    public static function forFormat(string $date, ?string $format): InvalidDate
    {
        $message = "Could not construct a date from `{$date}`";

        if ($format) {
            $message .= " with format `{$format}`";
        }

        return new static($message);
    }
}

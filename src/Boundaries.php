<?php

namespace Spatie\Period;

class Boundaries
{
    private const EXCLUDE_NONE = 0;
    private const EXCLUDE_START = 2;
    private const EXCLUDE_END = 4;
    private const EXCLUDE_ALL = 6;

    private function __construct(
        private int $mask
    ) {
    }

    public static function EXCLUDE_NONE(): self
    {
        return new self(self::EXCLUDE_NONE);
    }

    public static function EXCLUDE_START(): self
    {
        return new self(self::EXCLUDE_START);
    }

    public static function EXCLUDE_END(): self
    {
        return new self(self::EXCLUDE_END);
    }

    public static function EXCLUDE_ALL(): self
    {
        return new self(self::EXCLUDE_ALL);
    }

    public function startExcluded(): bool
    {
        return self::EXCLUDE_START & $this->mask;
    }

    public function startIncluded(): bool
    {
        return ! $this->startExcluded();
    }

    public function endExcluded(): bool
    {
        return self::EXCLUDE_END & $this->mask;
    }

    public function endIncluded(): bool
    {
        return ! $this->endExcluded();
    }
}

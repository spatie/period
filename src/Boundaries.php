<?php

namespace Spatie\Period;

use DateTimeImmutable;

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

    public static function fromString(string $startBoundary, string $endBoundary): self
    {
        return match ("{$startBoundary}{$endBoundary}") {
            '[]' => self::EXCLUDE_NONE(),
            '[)' => self::EXCLUDE_END(),
            '(]' => self::EXCLUDE_START(),
            '()' => self::EXCLUDE_ALL(),
        };
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

    public function realStart(DateTimeImmutable $includedStart, Precision $precision): DateTimeImmutable
    {
        if ($this->startIncluded()) {
            return $includedStart;
        }

        return $precision->decrement($includedStart);
    }

    public function realEnd(DateTimeImmutable $includedEnd, Precision $precision): DateTimeImmutable
    {
        if ($this->endIncluded()) {
            return $includedEnd;
        }

        return $precision->increment($includedEnd);
    }
}

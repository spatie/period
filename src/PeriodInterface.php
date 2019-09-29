<?php

namespace Spatie\Period;

use DateTimeImmutable;
use DateTimeInterface;

interface PeriodInterface
{
    public function __construct(
        DateTimeImmutable $start,
        DateTimeImmutable $end,
        ?int $precisionMask = null,
        ?int $boundaryExclusionMask = null
    );

    public static function make(
        $start,
        $end,
        ?int $precisionMask = null,
        ?int $boundaryExclusionMask = null,
        ?string $format = null
    ): PeriodInterface;

    public function startIncluded(): bool;

    public function startExcluded(): bool;

    public function endIncluded(): bool;

    public function endExcluded(): bool;

    public function getStart(): DateTimeImmutable;

    public function getIncludedStart(): DateTimeImmutable;

    public function getEnd(): DateTimeImmutable;

    public function getIncludedEnd(): ?DateTimeImmutable;

    public function length(): ?int;

    public function overlapsWith(PeriodInterface $period): bool;

    public function touchesWith(PeriodInterface $period): bool;

    public function startsBefore(DateTimeInterface $date): bool;

    public function startsBeforeOrAt(DateTimeInterface $date): bool;

    public function startsAfter(DateTimeInterface $date): bool;

    public function startsAfterOrAt(DateTimeInterface $date): bool;

    public function startsAt(DateTimeInterface $date): bool;

    public function endsBefore(DateTimeInterface $date): bool;

    public function endsBeforeOrAt(DateTimeInterface $date): bool;

    public function endsAfter(DateTimeInterface $date): bool;

    public function endsAfterOrAt(DateTimeInterface $date): bool;

    public function endsAt(DateTimeInterface $date): bool;

    public function contains(DateTimeInterface $date): bool;

    public function equals(PeriodInterface $period): bool;

    public function gap(PeriodInterface $period): ?Period;

    public function overlapSingle(PeriodInterface $period): ?Period;

    public function overlap(PeriodInterface ...$periods): PeriodCollection;

    public function overlapAll(PeriodInterface ...$periods): Period;

    public function diffSingle(PeriodInterface $period): PeriodCollection;

    public function diff(PeriodInterface ...$periods): PeriodCollection;

    public function getPrecisionMask(): int;
}

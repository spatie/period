<?php

namespace Spatie\Period;

class Visualizer
{
    /**
     * Options used in configuring the visualization.
     *
     * - int width:
     * Determines the output size of the visualization
     * Note: This controls the width of the bars only.
     *
     * @var array
     */
    private $options;

    /**
     * Create a new visualizer.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Builds a string to visualize one or more
     * periods and/or collections in a more
     * human readable / parsable manner.
     *
     * Keys are used as identifiers in the output
     * and the periods are represented with bars.
     *
     * This visualizer is capable of generating
     * output like the following:
     *
     * A       [========]
     * B                    [==]
     * C                            [=====]
     * CURRENT        [===============]
     * OVERLAP        [=]   [==]    [=]
     *
     * @param array|Period[]|PeriodCollection[] $blocks
     * @return string
     */
    public function visualize(array $blocks): string
    {
        $matrix = $this->matrix($blocks);

        $nameLength = max(...array_map('strlen', array_keys($matrix)));

        $lines = [];

        foreach ($matrix as $name => $row) {
            $lines[] = vsprintf('%s    %s', [
                str_pad($name, $nameLength, ' '),
                $this->toBars($row),
            ]);
        }

        return implode("\n", $lines);
    }

    /**
     * Build a 2D table such that:
     * - There's one row for every block.
     * - There's one column for every unit of width.
     * - Each cell is true when a period is active for that unit.
     * - Each cell is false when a period is not active for that unit.
     *
     * @param array $blocks
     * @return array
     */
    private function matrix(array $blocks): array
    {
        $width = $this->options['width'];

        $matrix = array_fill(0, count($blocks), array_fill(0, $width, false));
        $matrix = array_combine(array_keys($blocks), array_values($matrix));

        $bounds = $this->bounds($blocks);

        foreach ($blocks as $name => $block) {
            if ($block instanceof Period) {
                $matrix[$name] = $this->populateRow($matrix[$name], $block, $bounds);
            } elseif ($block instanceof PeriodCollection) {
                foreach ($block as $period) {
                    $matrix[$name] = $this->populateRow($matrix[$name], $period, $bounds);
                }
            }
        }

        return $matrix;
    }

    /**
     * Get the start / end coordinates for a given period.
     *
     * @param Period $period
     * @param Period $bounds
     * @param int $width
     * @return array
     */
    private function coords(Period $period, Period $bounds, int $width): array
    {
        $boundsStart = $bounds->getStart()->getTimestamp();
        $boundsEnd = $bounds->getEnd()->getTimestamp();
        $boundsLength = $boundsEnd - $boundsStart;

        // Get the bounds
        $start = $period->getStart()->getTimestamp() - $boundsStart;
        $end = $period->getEnd()->getTimestamp() - $boundsStart;

        // Rescale from timestamps to width units
        $start *= $width / $boundsLength;
        $end *= $width / $boundsLength;

        // Cap at integer intervals
        $start = floor($start);
        $end = ceil($end);

        return [$start, $end];
    }

    /**
     * Populate a row with true values
     * where periods are active.
     *
     * @param array $row
     * @param Period $period
     * @param Period $bounds
     * @return array
     */
    private function populateRow(array $row, Period $period, Period $bounds): array
    {
        $width = $this->options['width'];

        [$startIndex, $endIndex] = $this->coords($period, $bounds, $width);

        for ($i = 0; $i < $width; $i++) {
            if ($startIndex <= $i && $i < $endIndex) {
                $row[$i] = true;
            }
        }

        return $row;
    }

    /**
     * Get the bounds encompassing all visualized periods.
     *
     * @param array $blocks
     * @return Period|null
     */
    private function bounds(array $blocks): ?Period
    {
        $periods = new PeriodCollection();

        foreach ($blocks as $block) {
            if ($block instanceof Period) {
                $periods[] = $block;
            } elseif ($block instanceof PeriodCollection) {
                foreach ($block as $period) {
                    $periods[] = $period;
                }
            }
        }

        return $periods->boundaries();
    }

    /**
     * Turn a series of true/false values into bars
     * representing the start/end of periods.
     *
     * @param array $row
     * @return string
     */
    private function toBars(array $row): string
    {
        $tmp = '';

        for ($i = 0, $l = count($row); $i < $l; $i++) {
            $prev = $row[$i - 1] ?? null;
            $curr = $row[$i];
            $next = $row[$i + 1] ?? null;

            // Small state machine to build the string
            switch (true) {
                // The current period is only one unit long so display a "="
                case $curr && $curr !== $prev && $curr !== $next:
                    $tmp .= '=';
                    break;

                // We've hit the start of a period
                case $curr && $curr !== $prev && $curr === $next:
                    $tmp .= '[';
                    break;

                // We've hit the end of the period
                case $curr && $curr !== $next:
                    $tmp .= ']';
                    break;

                // We're adding segments to the current period
                case $curr && $curr === $prev:
                    $tmp .= '=';
                    break;

                // Otherwise it's just empty space
                default:
                    $tmp .= ' ';
                    break;
            }
        }

        return $tmp;
    }
}

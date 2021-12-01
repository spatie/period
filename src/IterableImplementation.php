<?php

namespace Spatie\Period;

trait IterableImplementation
{
    protected $position = 0;

    public function offsetGet(mixed $offset): mixed
    {
        return $this->periods[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->periods[] = $value;

            return;
        }

        $this->periods[$offset] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->periods);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->periods[$offset]);
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return array_key_exists($this->position, $this->periods);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->periods);
    }
}

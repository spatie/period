<?php

namespace Spatie\Period;

trait IterableImplementation
{
    protected $position = 0;

    public function offsetGet($offset)
    {
        return $this->periods[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->periods[] = $value;

            return;
        }

        $this->periods[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->periods);
    }

    public function offsetUnset($offset)
    {
        unset($this->periods[$offset]);
    }

    public function next()
    {
        $this->position++;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return array_key_exists($this->position, $this->periods);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->periods);
    }
}

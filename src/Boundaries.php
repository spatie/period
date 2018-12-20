<?php

namespace Spatie\Period;

interface Boundaries
{
    const EXCLUDE_NONE = 0;
    const EXCLUDE_START = 2;
    const EXCLUDE_END = 4;
    const EXCLUDE_ALL = 6;
}

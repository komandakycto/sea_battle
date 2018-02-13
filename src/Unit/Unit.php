<?php

namespace SeaBattle\Unit;

abstract class Unit
{
    /**
     * @var int Unit length
     */
    protected $size;

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * @return int Unit length
     */
    public function getSize(): int
    {
        return $this->size;
    }
}

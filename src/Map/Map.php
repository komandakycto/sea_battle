<?php

namespace SeaBattle\Map;

use SeaBattle\Unit\Ship;
use SeaBattle\Unit\Unit;

class Map
{
    /**
     * @var int Map size
     */
    private $size;
    /**
     * @var int[] Array of available coordinates with status
     */
    private $mapPoints;
    /**
     * Free point on the map
     */
    const EMPTY_POINT = 0;
    /**
     * Point with ship
     */
    const SHIP_POINT = 1;
    /**
     * Border ship point
     */
    const BORDER_ZONE = 2;
    /**
     * @var Unit[] Array of available units
     */
    private $units = [];

    const HORIZONTAL = 1;
    const VERTICAL = 2;

    private $reCount = 0;

    public function __construct(int $size = 10)
    {
        $this->size = $size;

        //generate coordinates
        for ($i = 1; $i <= $size; $i++) {
            for ($j = 1; $j <= $size; $j++) {
                $this->mapPoints["$i:$j"] = self::EMPTY_POINT;
            }
        }
    }

    /**
     * Add units on the available list
     *
     * @return $this
     */
    public function initUnits()
    {
        $this->units = [
            new Ship(1),
            new Ship(1),
            new Ship(1),
            new Ship(1),
            new Ship(2),
            new Ship(2),
            new Ship(2),
            new Ship(3),
            new Ship(3),
            new Ship(4),
        ];

        //for more randomize
        shuffle($this->units);

        return $this;
    }

    /**
     * Place available units on the map
     *
     * @return $this
     */
    public function placeUnits()
    {
        foreach ($this->units as $unit) {
            $this->place($unit);
        }

        return $this;
    }

    private function place(Unit $unit): void
    {
        do {
            if ($this->reCount === 50) {
                throw new \RuntimeException("Invalid configuration units and map.");
            }

            $startX = random_int(1, $this->size);
            $startY = random_int(1, $this->size);

            $this->reCount++;
        } while ($this->mapPoints["$startX:$startY"] !== self::EMPTY_POINT);

        $unitSize = $unit->getSize();

        $availableDirections = [];
        if ($this->canPlace([$startX, $startY], $unitSize, self::HORIZONTAL)) {
            $availableDirections[] = self::HORIZONTAL;
        }

        if ($this->canPlace([$startX, $startY], $unitSize, self::VERTICAL)) {
            $availableDirections[] = self::VERTICAL;
        }

        if (count($availableDirections) > 1) {
            $selectedDirection = random_int(min($availableDirections), max($availableDirections));
        } elseif (count($availableDirections) === 1) {
            $selectedDirection = $availableDirections[0];
        } else {
            $this->place($unit);

            return;
        }

        $this->markShip([$startX, $startY], $unitSize, $selectedDirection);
        $this->reCount = 0;
    }

    private function canPlace(array $coordinates, $unitSize, $direction): bool
    {
        $point = $direction === self::HORIZONTAL ? $coordinates[1] : $coordinates[0];

        if ($point + ($unitSize - 1) > $this->size) {
            return false;
        }

        for ($i = $point; $i <= $point + ($unitSize - 1); $i++) {
            $pointCoordinate = $direction === self::HORIZONTAL ? "{$coordinates[0]}:$i" : "$i:{$coordinates[1]}";
            if ($this->mapPoints[$pointCoordinate] !== self::EMPTY_POINT) {
                return false;
            }
        }

        return true;
    }

    private function markShip(array $coordinates, int $unitSize, int $direction): void
    {
        if ($direction === self::HORIZONTAL) {
            list($x, $i) = $coordinates;
            $currentPoint = $coordinates[1];
        } else {
            list($i, $x) = $coordinates;
            $currentPoint = $coordinates[0];
        }

        for (--$i; $i < $currentPoint + ($unitSize + 1); $i++) {
            if (!isset($this->mapPoints[$this->pointKey($x, $i, $direction)])) {
                continue;
            }

            if ($i >= $currentPoint && $i <= ($currentPoint + $unitSize - 1)) {
                $this->mapPoints[$this->pointKey($x, $i, $direction)] = self::SHIP_POINT;
            } else {
                $this->mapPoints[$this->pointKey($x, $i, $direction)] = self::BORDER_ZONE;
            }

            if ($direction === self::HORIZONTAL) {
                $this->markPointOffset($x - 1, $i);
                $this->markPointOffset($x + 1, $i);
            } else {
                $this->markPointOffset($i, $coordinates[1] - 1);
                $this->markPointOffset($i, $coordinates[1] + 1);
            }
        }
    }

    private function pointKey(int $x, int $y, int $direction): string
    {
        if ($direction === self::HORIZONTAL) {
            return "$x:$y";
        } else {
            return "$y:$x";
        }
    }

    private function markPointOffset(int $x, int $y): void
    {
        if (isset($this->mapPoints["$x:$y"])) {
            $this->mapPoints["$x:$y"] = self::BORDER_ZONE;
        }
    }

    public function render()
    {
        echo '
  _____              ____        _   _   _      
 / ____|            |  _ \      | | | | | |     
| (___   ___  __ _  | |_) | __ _| |_| |_| | ___ 
 \___ \ / _ \/ _` | |  _ < / _` | __| __| |/ _ \
 ____) |  __/ (_| | | |_) | (_| | |_| |_| |  __/
|_____/ \___|\__,_| |____/ \__,_|\__|\__|_|\___|
                                                
                                               
        ' . PHP_EOL;
        for ($i = 1; $i <= $this->size; $i++) {
            for ($j = 1; $j <= $this->size; $j++) {
                if ($this->mapPoints["$i:$j"] !== self::SHIP_POINT) {
                    echo '.  ';
                } else {
                    echo 'X  ';
                }
            }

            echo PHP_EOL;
        }
    }
}

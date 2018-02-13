<?php

namespace SeaBattle\Facade;

use SeaBattle\Map\Map;

class GameFacade
{
    public function run()
    {
        (new Map())->initUnits()->placeUnits()->render();
    }
}

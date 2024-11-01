<?php

namespace Smartcat\Includes\Plugin;

use Smartcat\Includes\Services\Plugin\Migrations;

class Deactivator
{
    public static function deactivate()
    {
        $migrations = new Migrations();
        // $migrations->drop();
	}
}
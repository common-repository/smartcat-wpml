<?php

namespace Smartcat\Includes\Plugin;

use Smartcat\Includes\Services\Plugin\Migrations;

class Activator
{
    public static function activate()
    {
        $migrations = new Migrations();
        $migrations->run();
        add_option("smartcat_wpml_secret", NULL);
        add_option("smartcat_wpml_acf_using", 0);
    }
}
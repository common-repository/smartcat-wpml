<?php

namespace Smartcat\Includes\Plugin;

class PluginMultilingual
{
    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(
            'smartcat-wpml',
            false,
            dirname(plugin_basename(__FILE__)) . '/../../languages/'
        );
    }

    public static function e($text)
    {
        _e($text, 'smartcat-wpml');
    }

    public static function _e($text)
    {
        return __($text, 'smartcat-wpml');
    }
}
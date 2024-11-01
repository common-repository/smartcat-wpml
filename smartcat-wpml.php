<?php
/**
 * Plugin Name:       Smartcat Integration for WPML
 * Plugin URI:        https://smartcat.com
 * Description:       Smartcat Integration Add-on allows you to synchronize your site content for localization with Smartcat
 * Version:           3.1.55
 * Author:            Smartcat
 * Author URI:        #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smartcat-wpml
 * Domain Path:       /languages
 */

if (!defined('WPINC')) {
    die;
}

define('SMARTCAT_WPML_PLUGIN_PATH', __FILE__);

const SMARTCAT_WPML_VERSION = '3.1.55';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/workspace.php';

require_once ABSPATH . '/wp-admin/includes/taxonomy.php';

require_once __DIR__ . '/autoload.php';

require_once __DIR__ . '/functions.php';

register_activation_hook(__FILE__, 'smartcatActivatePlugin');
register_deactivation_hook(__FILE__, 'smartcatDeactivatePlugin');

run();

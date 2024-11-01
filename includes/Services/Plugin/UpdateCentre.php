<?php

namespace Smartcat\Includes\Services\Plugin;

use Smartcat\Includes\Services\Tools\Notice;

class UpdateCentre
{
    public static function status()
    {
        $updatePlugins = get_site_transient('update_plugins');
        if (isset($updatePlugins->response['smartcat-wpml/smartcat-wpml.php'])) {
            $latestVersion = $updatePlugins->response['smartcat-wpml/smartcat-wpml.php']->new_version;
            $upgradeUrl = admin_url("/admin.php?page=smartcat-wpml&action=upgrade");
            Notice::warn("A new version of the plugin is available: $latestVersion. <a href='$upgradeUrl'>Update</a> now.");
        }
    }
}
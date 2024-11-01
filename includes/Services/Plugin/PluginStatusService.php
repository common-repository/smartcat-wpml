<?php

namespace Smartcat\Includes\Services\Plugin;

use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PluginStatusService
{
    private $wpmlService;

    public function __construct(
        WpmlInterface $wpmlService
    )
    {
        $this->wpmlService = $wpmlService;
    }

    public function statusInformation(): array
    {
        return [
            'pluginVersion' => SMARTCAT_WPML_VERSION,
            'locales' => $this->getLocales(),
            'secretKeyIsValid' => $this->isValidSecret(),
            'friendlyUrlEnabled' => !empty(get_option('permalink_structure')),
            'wpmlInstalled' => function_exists('icl_object_id'),
            'elementorInstalled' => is_elementor_installed()
        ];
    }

    private function isValidSecret(): bool
    {
        return ($_SERVER['HTTP_X_SMARTCAT_SECRET'] ?? NULL) === get_option('smartcat_wpml_secret');
    }

    private function getLocales(): array
    {
        $locales = $this->wpmlService->getActiveLocales();
        return array_map(function ($locale) {
            return $locale['locale'];
        }, $locales);
    }
}
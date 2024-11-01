<?php

namespace Smartcat\Includes\Services\WPML;

use function WPML\Container\make;

class WpmlPageBuilder
{
    public function registerAllStrings(\WP_Post $post)
    {
        $pageBuilderIntegration = $this->getPageBuilderIntegration();
        $pageBuilderIntegration->queue_save_post_actions($post->ID, $post);

        foreach ($pageBuilderIntegration->get_save_post_queue() as $post) {
            $pageBuilderIntegration->register_all_strings_for_translation($post);
        }
    }

    public function getPageBuilderIntegration(): \WPML_PB_Integration
    {
        do_action('wpml_load_page_builders_integration');

        $pageBuilderStrategies = [];

        $required = apply_filters('wpml_page_builder_support_required', []);

        foreach ($required as $plugin) {
            $pageBuilderStrategies[] = new \WPML_PB_API_Hooks_Strategy($plugin);
        }

        $pageBuilderConfigImport = new \WPML_PB_Config_Import_Shortcode(new \WPML_ST_Settings());
        $pageBuilderConfigImport->add_hooks();

        if ($pageBuilderConfigImport->has_settings()) {
            $strategy = new \WPML_PB_Shortcode_Strategy(new \WPML_Page_Builder_Settings());
            $strategy->add_shortcodes($pageBuilderConfigImport->get_settings());
            $pageBuilderStrategies[] = $strategy;

            if (defined('WPML_MEDIA_VERSION') && $pageBuilderConfigImport->get_media_settings()) {
                $shortcodesMediaHooks = new \WPML_Page_Builders_Media_Hooks(
                    new \WPML_Page_Builders_Media_Shortcodes_Update_Factory($pageBuilderConfigImport),
                    'shortcodes'
                );
                $shortcodesMediaHooks->add_hooks();
            }
        }

        $pageBuilderIntegration = make('WPML_PB_Integration');

        if ($pageBuilderStrategies) {
            $factory = make('WPML_PB_Factory');
            $pageBuilderIntegration->add_hooks();
            foreach ($pageBuilderStrategies as $strategy) {
                $strategy->set_factory($factory);
                $pageBuilderIntegration->add_strategy($strategy);
            }
        }

        return $pageBuilderIntegration;
    }
}
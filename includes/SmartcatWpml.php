<?php

namespace Smartcat\Includes;

use Smartcat\Includes\Controllers\AppController;
use Smartcat\Includes\Controllers\SettingsController;
use Smartcat\Includes\Plugin\PluginLoader;
use Smartcat\Includes\Plugin\PluginMultilingual;
use Smartcat\Includes\Services\Cron\CronHandler;
use Smartcat\Includes\Services\Plugin\Migrations;
use Smartcat\Publicly\SmartcatPublic;
use SmartcatAdmin;

class SmartcatWpml
{
    protected $loader;
    protected $pluginName;
    protected $version;
    protected $admin;
    /** @var CronHandler */
    private $cronHandler;

    public function __construct()
    {
        $this->version = SMARTCAT_WPML_VERSION;
        $this->pluginName = 'smartcat-wpml';

        $this->cronHandler = new CronHandler();

        $this->loadDependencies();
        $this->setLocale();
        $this->defineAdminHooks();
        $this->definePublicHooks();
    }

    private function loadDependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/Plugin/PluginLoader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/Plugin/PluginMultilingual.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/SmartcatAdmin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/SmartcatPublic.php';
        $this->loader = new PluginLoader();
    }

    private function setLocale()
    {
        $pluginMultilingual = new PluginMultilingual();
        $this->loader->add_action('plugins_loaded', $pluginMultilingual, 'loadPluginTextdomain');
    }

    private function defineAdminHooks()
    {
        $this->admin = new SmartcatAdmin($this->getPluginName(), $this->getVersion());

        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');

        $migrations = new Migrations();
        $migrations->check();

        // Settings -> Smartcat Integration for WPML
        $this->loader->add_action('admin_menu', $this->admin, 'addPluginAdminMenu');

        $pluginBasename = plugin_basename(plugin_dir_path(__DIR__) . $this->pluginName . '.php');

        $this->loader->add_filter('plugin_action_links_' . $pluginBasename, $this->admin, 'addActionLinks');
        $this->loader->add_action('add_meta_boxes', $this->admin, 'registerSmartcatMetabox');

        $wpmlPostTypes = smartcat_post_type()->getWpmlPostTypes();

        foreach ($wpmlPostTypes as $key => $type) {
            $this->loader->add_filter("bulk_actions-edit-$key", $this->admin, 'addBulkActionToPostsPage');
        }

        foreach ($wpmlPostTypes as $key => $type) {
            $this->loader->add_filter("handle_bulk_actions-edit-$key", $this->admin, 'handleBulkActionSmartcatCreateTR', 10, 3);
        }

        // $this->loader->add_action('delete_post', $this->admin, 'deleteTranslation', 10, 1);
        $this->loader->add_action('plugins_loaded', $this->admin, 'checkOrUpdateMigrations');
        $this->loader->add_action('plugins_loaded', $this->admin, 'defaultEditorOption');

        $this->registerAdminActions();
        $this->notices();
    }

    private function registerAdminActions()
    {
        $this->loader->add_action("admin_post_smartcat_update_options", $this->admin, "smartcatUpdateOptions");
        $this->loader->add_action("admin_post_smartcat_logout", $this->admin, "smartcatLogout");
        $this->loader->add_action("admin_post_smartcat_log_in", $this->admin, "logInToSmartcat");
        $this->loader->add_action('admin_post_smartcat_auth_host', $this->admin, 'updateDebugSettings');

        $appController = new AppController();
        $this->loader->add_action('wp_ajax_smartcat_create_translation_request', $appController, 'createTranslationRequest');
        $this->loader->add_action('wp_ajax_smartcat_get_translations', $appController, 'exportTranslations');
        $this->loader->add_action('wp_ajax_smartcat_update_source_content', $appController, 'updateSourceContent');
        $this->loader->add_action('wp_ajax_smartcat_add_language_to_translation_request', $appController, 'addLanguageToTranslationRequest');
        $this->loader->add_action('wp_ajax_smartcat_remove_language', $appController, 'removeLanguageFromTranslationRequest');
        $this->loader->add_action('wp_ajax_smartcat_remove_translation_request', $appController, 'removeTranslationRequest');
        $this->loader->add_action('wp_ajax_smartcat_remove_post_from_translation_request', $appController, 'removePostFromTranslationRequest');
        $this->loader->add_action('wp_ajax_smartcat_translation_request_info', $appController, 'translationRequestInfo');
        $this->loader->add_action('wp_ajax_smartcat_fetch_projects', $appController, 'getProjects');
        $this->loader->add_action('wp_ajax_smartcat_get_translations_by_post_and_locale', $appController, 'exportTranslationsByPostAndLocale');

        // ui settings ajax requests
        $settingsController = new SettingsController();

        $this->loader->add_action('wp_ajax_smartcat_new_secret', $this->admin, 'newSecret');
        $this->loader->add_action('wp_ajax_smartcat_register_credentials', $settingsController, 'registerCredentials');
        $this->loader->add_action('wp_ajax_smartcat_save_settings', $settingsController, 'saveSettings');

    }

    private function definePublicHooks()
    {
        $pluginPublic = new SmartcatPublic();
        $this->loader->add_action('rest_api_init', $pluginPublic, 'routes');
    }

    public function run()
    {
        $this->registerCronTasks();

        $this->loader->run();

        $this->cronHandler->init();
    }

    private function registerCronTasks()
    {
        $this->loader->add_filter('cron_schedules', $this->cronHandler, 'intervals');

        if ($this->cronHandler->isEnabledGetTranslations()) {
            $this->loader->add_action($this->cronHandler->getTranslationsTaskName(), $this->cronHandler, 'getTranslationsTask');
        }
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    private function notices()
    {
        $this->loader->add_action('admin_notices', $this->admin, 'displayPluginAdminNotices');

        if (!function_exists('icl_object_id')) {
            $this->loader->add_action('admin_notices', $this->admin, 'displayWpmlNotInstalledNotice');
        }

        if (empty(get_option("permalink_structure"))) {
            $this->loader->add_action('admin_notices', $this->admin, 'displayFriendlyUrlNotice');
        }
    }
}

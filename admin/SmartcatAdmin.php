<?php

use Smartcat\Includes\Services\Plugin\Migrations;
use Smartcat\Includes\Services\Plugin\UpdateCentre;
use Smartcat\Includes\Services\Posts\PostTypeService as PostTypeService;
use Smartcat\Includes\Services\Tools\Notice as NoticeService;
use Smartcat\Includes\Services\Plugin\Settings as SettingsService;
use Smartcat\Includes\Plugin\PluginMultilingual as i18n;
use Smartcat\Includes\Views\FAQ as FAQView;
use Smartcat\Includes\Views\Metabox as MetaboxView;
use Smartcat\Includes\Views\Settings as SettingsView;
use Smartcat\Includes\Views\CreateTranslationRequest as CreateTranslationRequestView;
use Smartcat\Includes\Views\Dashboard as DashboardView;
use Smartcat\Includes\Views\Logs as LogsView;
use Smartcat\Includes\Views\TranslationRequestDetails as TranslationRequestDetailsView;

class SmartcatAdmin
{
    private $pluginName;
    private $version;

    /**
     * @var SettingsService
     */
    private $settings;

    /** @var PostTypeService */
    private $postTypesService;

    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version = $version;
        $this->settings = new SettingsService();
        $this->postTypesService = new PostTypeService();
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style($this->pluginName, plugin_dir_url(__FILE__) . 'assets/css/smartcat-wpml.css', [], $this->version);
        wp_enqueue_style("$this->pluginName-ui", plugin_dir_url(__FILE__) . 'assets/css/smartcat-ui.css', [], $this->version);
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script($this->pluginName, plugin_dir_url(__FILE__) . 'assets/js/smartcat-wpml.js', array('jquery'), $this->version, true);
        wp_enqueue_script("$this->pluginName-ui", plugin_dir_url(__FILE__) . 'assets/js/smartcat-ui.js', array('jquery'), $this->version, true);
    }

    public function handleBulkActionSmartcatCreateTR($redirectTo, $doAction, $postIds)
    {
        if ($doAction !== SMARTCAT_CREATE_TRANSLATION_BULK_ACTION) {
            return $redirectTo;
        }
        $redirectTo = admin_url("/admin.php?page=$this->pluginName-create-translation-request");
        $redirectTo = add_query_arg('posts', json_encode($postIds), $redirectTo);
        wp_redirect($redirectTo);
    }

    public function addBulkActionToPostsPage($bulkActions)
    {
        $bulkActions[SMARTCAT_CREATE_TRANSLATION_BULK_ACTION] = 'Translate selected posts';
        return $bulkActions;
    }

    public function registerSmartcatMetabox()
    {
        /** @var \SitePress $sitepress */
        global $sitepress;

        add_meta_box(
            'smartcat_metabox',
            'Smartcat',
            [$this, 'displayMetabox'],
            array_keys($sitepress->get_translatable_documents()),
            'side',
            'high'
        );
    }

    public function addPluginAdminMenu()
    {
        add_menu_page(
            'Smartcat Integration for WPML',
            'Smartcat',
            'edit_others_posts',
            $this->pluginName,
            [$this, 'displayPluginSettingsPage'],
            plugin_dir_url(__FILE__) . 'assets/img/icon.svg'
        );
        add_submenu_page(
            $this->pluginName,
            'Smartcat Integration for WPML',
            i18n::_e('Settings'),
            'publish_posts',
            $this->pluginName,
            [$this, 'displayPluginSettingsPage']
        );
        add_submenu_page(
            $this->pluginName,
            'Smartcat Integration for WPML',
            i18n::_e('Translation requests'),
            'publish_posts',
            "$this->pluginName-dashboard",
            [$this, 'displayTranslationRequestsDashboard']
        );
        add_submenu_page(
            $this->pluginName,
            'Smartcat Integration for WPML - Helper',
            i18n::_e('Helper'),
            'publish_posts',
            "$this->pluginName-faq",
            [$this, 'displayFaq']
        );
        add_submenu_page(
            "$this->pluginName-hidden",
            'Smartcat Integration for WPML',
            i18n::_e('Create translation request'),
            'publish_posts',
            "$this->pluginName-create-translation-request",
            [$this, 'displayCreateTranslationRequestPage']
        );
        add_submenu_page(
            "$this->pluginName-hidden",
            'Smartcat Integration for WPML',
            i18n::_e('Transaltion request details'),
            'publish_posts',
            "$this->pluginName-translation-request",
            [$this, 'displayTranslationRequestDetailsPage']
        );
        add_submenu_page(
            "$this->pluginName",
            'Smartcat Integration for WPML',
            i18n::_e('Logs'),
            'publish_posts',
            "$this->pluginName-logs",
            [$this, 'displayLogsPage']
        );
    }

    public function checkOrUpdateMigrations()
    {
        (new Migrations())->run();
    }

    public function defaultEditorOption()
    {
        if (function_exists('icl_object_id')) {
            global $sitepress;

            $optionKey = 'smartcat_always_wp_editor';
            $optionValue = get_option($optionKey, null);
            $tmSettings = $sitepress->get_setting('translation-management');
            $usingNativeEditor = $tmSettings[WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_GLOBAL_USE_NATIVE];

            if (is_null($optionValue)) {
                add_option($optionKey, 'enabled');
                $this->setNativeEditorToWpmlOptions();
            } elseif (sc_check_option($optionKey) && !$usingNativeEditor) {
                $this->setNativeEditorToWpmlOptions();
            }
        }
    }

    private function setNativeEditorToWpmlOptions()
    {
        /** @var \SitePress $sitepress */
        global $sitepress;

        $tmSettings = $sitepress->get_setting('translation-management');

        $tmSettings[WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_GLOBAL_USE_NATIVE] = true;
        $tmSettings[WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE] = [];
        $sitepress->set_setting('translation-management', $tmSettings, true);
        WPML_TM_Post_Edit_TM_Editor_Mode::delete_all_posts_option();
    }

    public function addActionLinks($links): array
    {
        $customLinks = [
            '<a href="' . admin_url('options-general.php?page=' . $this->pluginName) . '">' . __('Settings', $this->pluginName) . '</a>'
        ];
        return array_merge($customLinks, $links);
    }

    public function displayPluginSettingsPage()
    {
        (new SettingsView())->display();
    }

    public function displayTranslationRequestsDashboard()
    {
        (new DashboardView())->display();
    }

    public function displayFaq()
    {
        (new FAQView())->display();
    }

    public function displayCreateTranslationRequestPage()
    {
        (new CreateTranslationRequestView())->display();
    }

    public function displayTranslationRequestDetailsPage()
    {
        (new TranslationRequestDetailsView())->display();
    }

    public function displayLogsPage()
    {
        (new LogsView())->display();
    }

    /**
     * @return void
     */
    public function smartcatUpdateOptions()
    {
        $this->settings->options($this->pluginName);
    }

    public function smartcatLogout()
    {
        $this->settings->smartcatLogout($this->pluginName);
    }

    public function updateDebugSettings()
    {
        $this->settings->updateDebugSettings($this->pluginName);
    }

    /**
     * @return void
     */
    public function displayPluginAdminNotices()
    {
        UpdateCentre::status();
        $noticeStatus = sanitize_text_field($_REQUEST['smartcat_notice_status'] ?? NULL);
        if (!empty($noticeStatus)) {
            $noticeContent = sanitize_text_field($_REQUEST['smartcat_notice_content']);
            NoticeService::notice($noticeStatus, $noticeContent);
        }
    }

    /**
     * @return void
     */
    public function displayWpmlNotInstalledNotice()
    {
        NoticeService::error('WPML plugin is not installed or activated. Smartcat Integration Add-on is not active');
    }

    /**
     * @return void
     */
    public function displayFriendlyUrlNotice()
    {
        NoticeService::error("The site uses simple links without a Friendly URL. Activate Friendly URL for Smartcat Integration Add-on to work correctly. <a href='https://wordpress.org/support/article/using-permalinks/' target='_blank'>More</a>");
    }

    public function newSecret()
    {
        wp_send_json([
            'secret' => $this->settings->generateNewSecret()
        ]);
    }

    public function displayMetabox($post)
    {
        (new MetaboxView($post->ID))->display();
    }

    public function logInToSmartcat()
    {
        smartcat_api()->logInRedirect();
    }
}

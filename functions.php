<?php

use Smartcat\Includes\Services\API\HubClient;
use Smartcat\Includes\Services\API\SmartcatClient;
use Smartcat\Includes\Services\App\ContentService;
use Smartcat\Includes\Services\App\DocumentsService;
use Smartcat\Includes\Services\App\Helpers;
use Smartcat\Includes\Services\App\SmartcatProjectFactory;
use Smartcat\Includes\Services\App\TranslationRequestService;
use Smartcat\Includes\Services\Elementor\ElementorService;
use Smartcat\Includes\Services\Metadata\MetadataService;
use Smartcat\Includes\Services\Posts\DatabaseService as PostsDatabase;
use Smartcat\Includes\Services\Posts\PostTypeService;
use Smartcat\Includes\Services\Sentry\SentryService;
use Smartcat\Includes\Services\Wpml;
use Smartcat\Includes\Services\Tools\LocaleMapper;
use Smartcat\Includes\Services\Tools\Logger;
use Smartcat\Includes\Services\WPBakery\Builder;
use Smartcat\Includes\Views\UI\UI;

/**
 * @return Wpml
 * @deprecated Use sc_wpml()
 */
function smartcat_wpml(): Wpml
{
    return new Wpml();
}

function sc_wpml(): Wpml
{
    return new Wpml();
}

function smartcat_api(): SmartcatClient
{
    return new SmartcatClient();
}

function smartcat_hub_client()
{
    return new HubClient();
}

function smartcat_dm(): DocumentsManager
{
    return new DocumentsManager();
}

function smartcat_post_type()
{
    return new PostTypeService();
}


/**
 * @return Logger
 * @deprecated use sc_log()
 */
function smartcat_logger()
{
    return new Logger();
}

function is_elementor_installed(): bool
{
    return is_plugin_active('elementor/elementor.php')
        && class_exists('\Elementor\Plugin');
}

function sc_str_contains($haystack, $needle)
{
    return ('' === $needle || false !== strpos($haystack, $needle));
}

function smartcat_check_json_from_param($param): bool
{
    if (isset($_GET[$param])) {
        $data = json_decode($_GET[$param]);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (empty($data) || !is_array($data)) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function sc_locale(): LocaleMapper
{
    return new LocaleMapper();
}

function sc_bakery_builder(): Builder
{
    return new Builder();
}

function sc_built_with_bakery($postID): bool
{
    return Builder::isUsedBuilder($postID);
}

function sc_ui(): UI
{
    return new UI();
}

function sc_maybe_decode($maybeJsonString)
{
    return sc_is_json($maybeJsonString)
        ? json_decode($maybeJsonString, true)
        : [];
}

function sc_is_json($maybeJsonString): bool
{
    json_decode($maybeJsonString);
    return json_last_error() === JSON_ERROR_NONE;
}

function sc_translation_request_service(): TranslationRequestService
{
    return new TranslationRequestService(
        new ContentService(
            new MetadataService(),
            new PostsDatabase(),
            new ElementorService()
        ),
        new SmartcatProjectFactory(
            new HubClient(),
            new SmartcatClient()
        ),
        new HubClient(),
        new SmartcatClient(),
        new DocumentsService()
    );
}

function sc_app_helpers(): Helpers
{
    return new Helpers();
}

function sc_log(string $prefix = 'App'): Logger
{
    return new Logger($prefix);
}

function sc_sentry(): SentryService
{
    return new SentryService();
}

function sc_check_option(string $name): bool
{
    $value = get_option($name);

    return $value === '1' || $value === 'enabled';
}


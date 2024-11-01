<?php

namespace Smartcat\Includes\Services\Plugin;

use Smartcat\Includes\Services\API\SmartcatClient;

class Settings
{
    public function options(string $pluginName)
    {
        $updateOptionsNonce = sanitize_text_field($_POST["smartcat_update_options_nonce"]);
        if (isset($updateOptionsNonce) && wp_verify_nonce($updateOptionsNonce, 'smartcat_update_options_form_nonce')) {
            $noticeStatus = 'success';
            $noticeContent = 'Settings have been saved';

            $secret = sanitize_text_field($_POST["secret"]);
            $acf = sanitize_text_field($_POST["acf"]);

            if (!empty($secret) && iconv_strlen($secret) === 30) {
                update_option('smartcat_wpml_secret', $secret);
            } else {
                $noticeStatus = 'error';
                $noticeContent = 'Incorrect API key value';
            }

            if ($acf === 'on') {
                if (!class_exists('ACF')) {
                    $noticeStatus = 'error';
                    $noticeContent = 'You cannot enable the "Advanced Custom Fields" option because the ACF plugin is not installed or activated';
                } else {
                    update_option('smartcat_wpml_acf_using', 1);
                }
            } else {
                update_option('smartcat_wpml_acf_using', 0);
            }

            wp_redirect(
                esc_url_raw(
                    add_query_arg(["smartcat_notice_status" => $noticeStatus, "smartcat_notice_content" => $noticeContent], admin_url("options-general.php?page={$pluginName}"))
                )
            );
            die();
        } else {
            wp_die(__('Invalid nonce specified', $pluginName), __('Error', $pluginName), [
                'response' => 403,
                'back_link' => 'admin.php?page=' . $pluginName,
            ]);
        }
    }

    public function smartcatLogout(string $pluginName)
    {
        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppAccountLoggedOut', true);

        update_option('smartcat_account_id', NULL);
        update_option('smartcat_api_key', NULL);
        update_option('smartcat_hub_key', NULL);
        update_option('smartcat_api_host', NULL);
        update_option('smartcat_hub_host', NULL);

        wp_redirect(
            esc_url_raw(
                add_query_arg([], admin_url("options-general.php?page=$pluginName"))
            )
        );
    }

    public function updateDebugSettings(string $pluginName)
    {
        if ($_POST['host'] !== SMARTCAT_HOST && $_POST['host'] !== SMARTCAT_HOST_DEV) {
            update_option('smartcat_api_host', $_POST['host']);
        }

        update_option('smartcat_debug_mode', (int)isset($_POST['debug']));
        wp_redirect(
            esc_url_raw(
                add_query_arg([], admin_url("options-general.php?page=$pluginName&debug=true"))
            )
        );
    }

    public function generateNewSecret(): string
    {
        $secret = wp_generate_password(30, false);
        update_option('smartcat_wpml_secret', $secret);
        return $secret;
    }

    public function registerCredentials($server, $accountID, $apiKey)
    {
        $iHubHost = SMARTCAT_IHUB_HOST_DEV;

        if (!SmartcatClient::debugMode()) {
            $iHubHost = SMARTCAT_IHUB_HOSTS[$server];
        }

        $res = smartcat_hub_client()
            ->registerApiCredentials($iHubHost, $accountID, $apiKey);

        if (is_wp_error($res)) {
            return $res;
        }

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppAccountLoggedIn', true);

        update_option('smartcat_account_id', $accountID);
        update_option('smartcat_api_key', $apiKey);
        update_option('smartcat_hub_key', $res['registeredApiKeyId']);

        $smarcatHost = SmartcatClient::debugMode()
            ? SMARTCAT_HOST_DEV
            : SMARTCAT_HOSTS[$server];

        $iHubHost = SmartcatClient::debugMode()
            ? SMARTCAT_IHUB_HOST_DEV
            : SMARTCAT_IHUB_HOSTS[$server];

        update_option('smartcat_api_host', $smarcatHost);
        update_option('smartcat_hub_host', $iHubHost);

        return smartcat_hub_client()->workspaceInfo($accountID);
    }
}
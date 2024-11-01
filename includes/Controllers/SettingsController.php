<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Services\Plugin\Settings;

class SettingsController
{
    private $settings;

    public function __construct()
    {
        $this->settings = new Settings();
    }

    public function registerCredentials()
    {
        $errors = [];
        $server = $_POST['server'] ?? null;
        $accountId = $_POST['accountId'] ?? null;
        $apiKey = $_POST['apiKey'] ?? null;

        if (!in_array($server, array_keys(SMARTCAT_IHUB_HOSTS))) {
            $errors[] = [
                'field' => 'server',
                'message' => ''
            ];
        }

        if (!preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}}?$/', $accountId)) {
            $errors[] = [
                'field' => 'accountId',
                'message' => 'Please check your account ID and try again'
            ];
        }

        if (!is_string($apiKey) || empty($apiKey)) {
            $errors[] = [
                'field' => 'apiKey',
                'message' => 'Please check your API key and try again'
            ];
        }

        if (!empty($errors)) {
            wp_send_json_error($errors, 422);
        } else {
            $result = $this->settings
                ->registerCredentials(
                    $server,
                    $accountId,
                    $apiKey
            );

            if (is_wp_error($result)) {
                wp_send_json_error([
                    [
                        'field' => 'accountId',
                        'message' => 'Please check your account ID and try again'
                    ],
                    [
                        'field' => 'apiKey',
                        'message' => 'Please check your API key and try again'
                    ]
                ], 422);
            } else {
                wp_send_json($result);
            }
        }
    }

    public function saveSettings()
    {
        $isAlwaysNativeEditor = $_POST['isAlwaysNativeEditor'] ?? false;
        $isAutomaticallyGetTranslations = $_POST['isAutomaticallyGetTranslations'] ?? false;
        $isDisableWpmlStringsRegister = $_POST['isDisableWpmlStringsRegister'] ?? false;
        $numberOfItemsToReceiveTranslations = $_POST['numberOfItemsToReceiveTranslations'] ?? 5;

        update_option('smartcat_always_wp_editor', $isAlwaysNativeEditor === 'true' ? 'enabled' : 'disabled');
        update_option('sc_automatically_get_translations', $isAutomaticallyGetTranslations === 'true' ? 'enabled' : 'disabled');
        update_option('sc_disable_wpml_strings_register', $isDisableWpmlStringsRegister === 'true' ? 'enabled' : 'disabled');
        update_option('sc_number_of_items_to_receive_translations', $numberOfItemsToReceiveTranslations);
    }
}
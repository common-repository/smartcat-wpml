<?php

namespace Smartcat\Includes\Services\Metadata;

use Smartcat\Includes\Services\Interfaces\CustomFieldInterface;

class CustomField implements CustomFieldInterface
{
    private $settings;

    public function __construct()
    {
        if (class_exists('WPML_Custom_Field_Setting_Factory') && class_exists('TranslationManagement')) {
            $this->settings = new \WPML_Custom_Field_Setting_Factory(new \TranslationManagement());
        }
    }

    public function getValue($key)
    {
        // TODO: реализация подъедет позже
    }

    public function isLocalizable($key): bool
    {
        return $this->settings->post_meta_setting($key)->status() === 2;
    }
}
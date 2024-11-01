<?php

namespace Smartcat\Includes\Services\Plugin;

class Options
{
    public static function maskSecret()
    {
        $apiSecretString = get_option("smartcat_wpml_secret");
        $apiSecretStringMask = substr($apiSecretString, 0, -10);
        $apiSecretStringMask .= $apiSecretStringMask != false ? "**********" : "";
        return $apiSecretStringMask;
    }

    public static function secret()
    {
        return get_option("smartcat_wpml_secret");
    }

    public static function maskApiSecret()
    {
        $apiSecretString = get_option("smartcat_api_key");
        $apiSecretStringMask = substr($apiSecretString, 0, -10);
        $apiSecretStringMask .= $apiSecretStringMask != false ? "**********" : "";
        return $apiSecretStringMask;
    }
}
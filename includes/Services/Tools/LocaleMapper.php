<?php

namespace Smartcat\Includes\Services\Tools;

class LocaleMapper
{
    const LOCALES = [
        'zh-hans' => 'zh-Hans',
        'zh-hant' => 'zh-Hant',
        'gb' => 'en-GB',
        'at' => 'de-AT',
        'ar-eg' => 'ar-EG',
        'az' => 'az-Cyrl',
        'ku' => 'kmr-Latn',
        'pt-br' => 'pt-BR',
        'pt-pt' => 'pt-PT',
        'sr' => 'sr-Cyrl',
        'uz' => 'uz-Latn',
        'af-za' => 'af',
        'mya' => 'my',
        // 	Be one solutions
        'br' => 'pt-BR',
        'cn' => 'zh-Hans',
        'jp' => 'ja',
        'mx' => 'es-MX',
    ];

    /**
     * @throws \Exception
     */
    public function map($locale)
    {
        $isCustomLocale = array_key_exists($locale, self::LOCALES);

        if ($isCustomLocale) {
            return self::LOCALES[$locale];
        }

        $languages = json_decode(file_get_contents( dirname(__DIR__, 4) . '/smartcat-wpml/languages/smartcat-languages.json'));

        $languageObjects = array_filter($languages, function ($language) use ($locale) {
            return str_replace(['-', '_'], '', strtolower($language->cultureName))
                === str_replace(['-', '_'], '', strtolower($locale));
        });

        $languageObjects = array_values($languageObjects);

        if(count($languageObjects) > 0) {
            return $languageObjects[0]->cultureName;
        }

        throw new \Exception("Language code $locale is not supported by Smartcat");
    }
}

<?php

namespace Smartcat\Includes\Services;

use SitePress;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;
use Smartcat\Includes\Services\WPML\WpmlContentService;
use Smartcat\Includes\Services\WPML\WpmlPageBuilder;

class Wpml implements WpmlInterface
{
    private $wpdb;

    /**
     * @var SitePress $sitePress
     */
    private $sitePress;

    public function __construct()
    {
        global $wpdb, $sitepress;
        $this->wpdb = $wpdb;
        $this->sitePress = $sitepress;
    }

    public function switchLang(string $lang)
    {
        global $sitepress;
        $sitepress->switch_lang($lang, true);
    }

    public function getTrid(int $postId, string $type = 'post_post')
    {
        return apply_filters('wpml_element_trid', NULL, $postId, $type);
    }

    public function getTranslations(int $trid, string $type = 'post_post'): array
    {
        return apply_filters('wpml_get_element_translations', NULL, $trid, $type);
    }

    public function getTargetElementId(int $originalPostId, string $lang, string $type = 'post_post')
    {
        $trid = $this->getTrid($originalPostId, $type);
        if (!is_null($trid)) {
            $translations = $this->getTranslations($trid, $type);
            if (isset($translations[$lang])) {
                return $translations[$lang]->element_id;
            }
        }
        return NULL;
    }

    public function addTranslation(int $elementId, int $originalElementId, string $targetLang, string $sourceLang, string $type = 'post_post')
    {
        $args = [
            'element_id' => $elementId,
            'language_code' => $targetLang,
            'element_type' => $type,
            'source_language_code' => $sourceLang,
            'trid' => $this->getTrid($originalElementId, $type)
        ];

        do_action('wpml_set_element_language_details', $args);
    }

    public function getIdsBySourceElements($ids, array $languages, string $type = NULL): array
    {
        $idsCollection = [];
        $ids = is_array($ids) ? $ids : [$ids];
        foreach ($ids as $id) {
            if (is_null($type)) {
                $type = "post_" . get_post_type($id);
            }
            $trid = $this->getTrid($id, $type);
            if (!empty($trid)) {
                $translations = $this->getTranslations($trid, $type);
                foreach ($translations as $lang => $translation) {
                    if (in_array($lang, $languages)) {
                        $idsCollection[] = $translation->element_id;
                    }
                }
            }
        }
        return $idsCollection;
    }

    public function getElementLanguages(int $postId, array $exceptions = [], string $type = 'post_post'): array
    {
        $trid = $this->getTrid($postId, $type);
        $translations = $this->getTranslations($trid, $type);
        $collection = [];
        foreach ($translations as $lang => $translation) {
            if (!in_array($lang, $exceptions)) {
                $collection[] = $lang;
            }
        }
        return $collection;
    }

    public function getActiveLocales(): array
    {
        $normalizeLanguages = [];
        foreach (apply_filters('wpml_active_languages', NULL, 'orderby=id&order=desc') as $language) {
            $arr = ["locale" => $language["code"]];
            if ((int)$language['active'] === 1) {
                $arr["isDefault"] = true;
            }
            $normalizeLanguages[] = $arr;
        }
        return $normalizeLanguages;
    }

    public function getPostLocale(int $postId)
    {
        $details = apply_filters('wpml_post_language_details', NULL, $postId);
        return $details['language_code'] ?? NULL;
    }

    public function getStrings(string $language, array $domains = []): array
    {
        if (!function_exists('icl_st_get_contexts')) {
            return [];
        }

        if (!class_exists(\WPML_ST_Initialize::class)) {
            return [];
        }

        $query = "
            SELECT *  
            FROM {$this->wpdb->prefix}icl_strings 
            WHERE language = %s 
            AND context IN (" . implode(', ', array_fill(0, count($domains), '%s')) . ")
        ";

        $strings = $this->wpdb->get_results(
            $this->wpdb->prepare($query, array_merge([$language], $domains))
        );

        return array_map(function ($string) {
            return [
                // TODO: add translationId
                'stringId' => $string->id,
                'name' => $string->name,
                'context' => $string->context,
                'content' => $string->value,
            ];
        }, $strings);
    }

    public function registerString(string $domain, string $name, string $language, $value)
    {
        do_action('wpml_register_single_string', $domain, $name, $value, NULL, $language);
    }

    public function getTranslatedPostIds(array $types, array $languages): array
    {
        $types = array_map(function ($type) {
            return "post_$type";
        }, $types);

        $typesIn = implode(', ', array_fill(0, count($types), '%s'));
        $languagesIn = implode(', ', array_fill(0, count($languages), '%s'));

        $sql = "SELECT `element_id` 
                FROM {$this->getTranslationsTable()} 
                WHERE `element_type` IN ($typesIn) 
                AND `language_code` IN ($languagesIn)";

        return $this->wpdb->get_col(
            $this->wpdb->prepare($sql, array_merge($types, $languages))
        );
    }

    private function getTranslationsTable(): string
    {
        return "{$this->wpdb->prefix}icl_translations";
    }

    public function getPostLanguageCode($postId)
    {
        $data = apply_filters('wpml_post_language_details', NULL, $postId);
        return !is_wp_error($data) ? $data['language_code'] : null;
    }

    public function getPostLanguageName($postId)
    {
        $data = apply_filters('wpml_post_language_details', NULL, $postId);
        return !is_wp_error($data) ? $data['display_name'] : null;
    }

    public function getActiveLanguages(): array
    {
        return apply_filters('wpml_active_languages', NULL);
    }

    public function makeDuplicate($originalPostId, $locale)
    {
        return $this->sitePress->make_duplicate($originalPostId, $locale);
    }

    public function getTranslationId(int $originalId, string $locale, string $typePrefix = 'post')
    {
        $type = $typePrefix . '_' . get_post_type($originalId);
        $trid = $this->getTrid($originalId, $type);
        if (!is_null($trid)) {
            $translations = $this->getTranslations($trid, $type);
            if (isset($translations[$locale])) {
                return $translations[$locale]->element_id;
            }
        }
        return NULL;
    }

    public function content(): WpmlContentService
    {
        return new WpmlContentService();
    }

    public function extractPackageId($itemId)
    {
        $result = false;

        if (is_string($itemId) && preg_match('#^' . 'package-string-' . '#', $itemId)) {
            $result = preg_replace('#^' . 'package-string-' . '([0-9]+)-([0-9]+)$#', '$1', $itemId, 1);
        }

        return is_numeric($result) ? $result : false;
    }

    public function isPackage($itemId): bool
    {
        return is_string($itemId) && preg_match('#^' . 'package-string-' . '#', $itemId);
    }

    public function getPackageKind($id): string
    {
        $query = "
            SELECT kind  
            FROM {$this->wpdb->prefix}icl_string_packages 
            WHERE ID = %d
        ";

        return $this->wpdb->get_col(
            $this->wpdb->prepare($query, $id)
        )[0] ?? 'Post content';
    }

    public function isCustomField($itemId): bool
    {
        return is_string($itemId) && preg_match('#^' . 'field-' . '#', $itemId);
    }

    public function pb(): WpmlPageBuilder
    {
        return new WpmlPageBuilder();
    }
}
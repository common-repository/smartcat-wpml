<?php

namespace Smartcat\Includes\Services\WPML;

use Smartcat\Includes\Services\WPML\Models\WpmlContentItem;

class WpmlContentService
{
    const SLUG_BASE = 'package-string-';

    /** @var \TranslationManagement */
    private $translationManager;

    private $isSkipImportingPackageStrings = false;


    public function __construct()
    {
        $this->translationManager = new \TranslationManagement();
        $isSkip = $_POST['skipImportingPackageStrings'] ?? false;

        if ($isSkip === 'true' || $isSkip === true || $isSkip === 1 || $isSkip === "1") {
            $this->isSkipImportingPackageStrings = true;
        } else {
            $this->isSkipImportingPackageStrings = false;
        }
    }

    public function get($postId): array
    {
        $package = $this->translationManager
            ->create_translation_package($postId);

        /** @var WpmlContentItem[] $items */
        $items = [];

        foreach ($package['contents'] as $key => $content) {
            // Skip numeric content for optimization
            if (is_numeric($content)) {
                continue;
            }

            $items[] = new WpmlContentItem(
                $key,
                $content['translate'],
                $content['data'], '',
                $content['format'] ?? null,
                $content['wrap_tag'] ?? null,
                true
            );
        }

        return $items;
    }

    /**
     * @param \WP_Post $originalPost
     * @param $translatedPostId
     * @param WpmlContentItem[] $contentItems
     * @param $locale
     * @return int
     */
    public function save(\WP_Post $originalPost, $translatedPostId, array $contentItems, $locale): int
    {
        /** @var \SitePress $sitepress */
        global $sitepress, $iclTranslationManagement, $wpml_post_translations, $wpdb;

        $translatedPostData = [];

        delete_post_meta($translatedPostId, '_icl_lang_duplicate_of');

        if ($translatedPostId) {
            $translatedPostData['ID'] = $translatedPostId;
        }

        foreach ($contentItems as $item) {
            switch ($item->getKey()) {
                case 'title':
                    $translatedPostData['post_title'] = $item->getTranslatedData();
                    break;
                case 'body':
                    $translatedPostData['post_content'] = $item->getTranslatedData();
                    break;
                case 'excerpt':
                    $translatedPostData['post_excerpt'] = $item->getTranslatedData();
                    break;
                case 'URL':
                    $translatedPostData['post_name'] = $item->getTranslatedData();
                    break;
                default:
                    break;
            }
        }

        $translatedPostData['post_author'] = $originalPost->post_author;
        $translatedPostData['post_type'] = $originalPost->post_type;

        if ($sitepress->get_setting('sync_comment_status')) {
            $translatedPostData['comment_status'] = $originalPost->comment_status;
        }

        if ($sitepress->get_setting('sync_ping_status')) {
            $translatedPostData['ping_status'] = $originalPost->ping_status;
        }

        if ($sitepress->get_setting('sync_page_ordering')) {
            $translatedPostData['menu_order'] = $originalPost->menu_order;
        }

        if ($sitepress->get_setting('sync_private_flag') && $originalPost->post_status == 'private') {
            $translatedPostData['post_status'] = 'private';
        }

        if ($sitepress->get_setting('sync_password') && $originalPost->post_password) {
            $translatedPostData['post_password'] = $originalPost->post_password;
        }

        if ($sitepress->get_setting('sync_post_date')) {
            $translatedPostData['post_date'] = $originalPost->post_date;
        }

        if ($originalPost->post_parent) {
            $parent_id = $wpml_post_translations->element_id_in($originalPost->post_parent, $locale);
        }

        if (isset($parent_id) && $sitepress->get_setting('sync_page_parent')) {
            $translatedPostData['post_parent'] = $parent_id;
            $translatedPostData['parent_id'] = $parent_id;
        }

        if (!$this->isSkipImportingPackageStrings) {
            foreach ($contentItems as $item) {
                do_action(
                    'wpml_add_string_translation',
                    $this->extractItemId($item->getKey()),
                    $locale,
                    $item->getTranslatedData(),
                    \WPML_TM_Page_Builders::TRANSLATION_COMPLETE, 1, 'local'
                );
            }
        } else {
            sc_log()->info('Skip importing package strings', [
                'value' => $this->isSkipImportingPackageStrings
            ]);
        }

        if ($translatedPostId) {
            if ($sitepress->get_setting('translated_document_page_url') !== 'translate') {
                $translatedPostData['post_name'] = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT post_name
                         FROM {$wpdb->posts}
                         WHERE ID=%d
                         LIMIT 1",
                        $translatedPostId
                    )
                );
            }

            $existing_post = get_post($translatedPostId);
            $translatedPostData['post_date'] = $existing_post->post_date;
            $translatedPostData['post_date_gmt'] = $existing_post->post_date_gmt;
        }

        $translatedPostId = wpml_get_create_post_helper()->insert_post($translatedPostData, $locale);

        icl_cache_clear($translatedPostData['post_type'] . 's_per_language');

        $sitepress->copy_custom_fields($originalPost->ID, $translatedPostId);

        // set specific custom fields
        $copied_custom_fields = array('_top_nav_excluded', '_cms_nav_minihome');
        foreach ($copied_custom_fields as $ccf) {
            $val = get_post_meta($originalPost->ID, $ccf, true);
            update_post_meta($translatedPostId, $ccf, $val);
        }

        // sync _wp_page_template
        if ($sitepress->get_setting('sync_page_template')) {
            $_wp_page_template = get_post_meta($originalPost->ID, '_wp_page_template', true);
            if (!empty($_wp_page_template)) {
                update_post_meta($translatedPostId, '_wp_page_template', $_wp_page_template);
            }
        }

        (new WpmlCustomFieldsService())
            ->save($contentItems, $translatedPostId, $originalPost->ID);

        return $translatedPostId;
    }

    public function extractItemId($itemKey)
    {
        $result = false;

        if (is_string($itemKey) && preg_match('#^' . self::SLUG_BASE . '#', $itemKey)) {
            $result = preg_replace('#^' . self::SLUG_BASE . '([0-9]+)-([0-9]+)$#', '$2', $itemKey, 1);
        }

        return is_numeric($result) ? $result : false;
    }
}

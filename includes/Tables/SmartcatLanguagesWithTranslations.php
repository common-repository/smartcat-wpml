<?php

namespace Smartcat\Includes\Tables;

use Smartcat\Includes\Plugin\PluginMultilingual as i18n;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class SmartcatLanguagesWithTranslations extends \WP_List_Table
{
    private $postId;

    protected function display_tablenav($which)
    {
        //
    }

    public function column_name($item)
    {
        ?>
        <label for="<?php echo $item['language_code'] ?>">
            <?php echo $item['translated_name'] ?>
        </label>
        <?php
    }

    public function column_translate($item)
    {
        ?>
        <input
            type="checkbox"
            in-request="<?php echo $item['in_translation_request'] ? 'true' : 'false' ?>"
            language-name="<?php echo $item['translated_name'] ?>"
            post-id="<?php echo $item['post_id'] ?>"
            class="language smartcat-language-with-tr"
            id="<?php echo $item['language_code'] ?>"
            value="<?php echo $item['language_code'] ?>"
            <?php echo $item['in_translation_request'] ? 'checked' : '' ?>
        >
        <?php
    }

    public function column_progress($item)
    {
        echo $item['in_translation_request'] ? $item['translation_progress'] . '%' : '-';
    }

    public function column_actions($item)
    {
        if ($item['in_translation_request']) {
        ?>
        <div class="smartcat-dropdown-menu">
            <span class="smartcat-dropdown-menu-title">Actions <span class="dashicons dashicons-arrow-down-alt2"></span></span>
            <div class="smartcat-dropdown-menu-actions">
                <a
                    href="<?php echo $this->getSmartcatDocumentLink($item['smartcat_document_id']) ?>"
                    class="smartcat-dropdown-menu-actions_item"
                    target="_blank"
                ><?php i18n::e('Edit in Smartcat'); ?></a>
                <a
                        href="<?php echo $item['has_translated_post'] ? get_edit_post_link($item['translated_post_id']) : '#' ?>"
                        class="smartcat-dropdown-menu-actions_item <?php echo !$item['translated_post_id'] ? 'disabled' : '' ?>"
                        target="_blank"
                ><?php i18n::e('Edit in Wordpress'); ?></a>
            </div>
        </div>
        <?php
        }

    }

    private function getSmartcatDocumentLink($documentId): string
    {
        $split = explode('_', $documentId);
        $id = $split[0];
        $lang = $split[1];
        return smartcat_api()::getAuthHost(true) . "/editor?documentId=$id&languageId=$lang";
    }

    public function prepare_items()
    {
        $languages = smartcat_wpml()->getActiveLanguages();
        $postLanguage = smartcat_wpml()->getPostLanguageCode($this->postId);

        $languages = array_filter($languages, function ($l) use ($postLanguage) {
            return $l['code'] !== $postLanguage;
        });

        $this->items = array_filter($languages, function ($l) {
            return $l['active'] !== '1';
        });

        $documents = smartcat_dm()->getPostDocuments($this->postId);

        $this->items = array_map(function ($l) use ($documents) {
            $document = array_filter($documents, function ($d) use ($l) {
                return $d->lang === $l['language_code'];
            });

            $document = array_shift($document);

            $l['in_translation_request'] = !empty($document);
            $l['has_translated_post'] = !is_null($document) && !is_null($document->translated_post_id);
            $l['translated_post_id'] = !is_null($document) ? $document->translated_post_id :  NULL;
            $l['smartcat_document_id'] = !is_null($document) ? $document->smartcat_document_id : NULL;
            $l['translation_progress'] = !is_null($document) ? $document->translation_progress : NULL;
            $l['post_id'] = $this->postId;

            return $l;
        }, $languages);

        $this->_column_headers = [$this->get_columns(),];
    }

    public function get_columns(): array
    {
        return [
            'name' => 'Language',
            'translate' => 'Translate',
            'progress' => 'Progress',
            'actions' => '',
        ];
    }

    /**
     * @param mixed $postId
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            default:
                return 'no value';
        }
    }
}
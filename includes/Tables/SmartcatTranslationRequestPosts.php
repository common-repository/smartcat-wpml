<?php

namespace Smartcat\Includes\Tables;

use Smartcat\Includes\Plugin\PluginMultilingual as i18n;
use Smartcat\Includes\Services\App\Models\TranslationRequest;

class SmartcatTranslationRequestPosts extends SmartcatTable
{
    private $sourceLanguage;
    /** @var TranslationRequest $translationRequest */
    private $translationRequest;

    public function __call($name, $arguments)
    {
        if (strpos($name, 'language') !== false) {
            $languageCode = explode('column_language_', $name)[1];
            $this->languageCheckbox($languageCode, $arguments[0]['id']);
        }
    }

    public function column_name($item)
    {
        ?>
        <a
            sc-post-id="<?php echo $item['id'] ?>"
            class="sc-post-in-translation-request"
            href="<?php echo get_edit_post_link($item['id']) ?>"
            target="_blank"
        >
            <?php echo $item['name'] ?>
        </a>
        <?php
    }

    public function column_actions($item)
    {
        ?>
        <div style="display: flex; align-items: center; justify-content: flex-end;">
            <div class="smartcat-spin" style="display: none" id="smartcat-spin-<?php echo $item['id'] ?>">
                <span class="dashicons dashicons-update"></span>
            </div>
        </div>
        <div class="smartcat-dropdown-menu" id="smartcat-dropdown-menu-<?php echo $item['id'] ?>">
            <span class="smartcat-dropdown-menu-title">
                <?php i18n::e('Actions'); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
            </span>
            <div class="smartcat-dropdown-menu-actions">
                <?php
                    if (false) {
                        ?>
                        <a href="#" class="smartcat-dropdown-menu-actions_item smartcat-get-translations-action" post-id="<?php echo $item['id'] ?>">
                            <span class="dashicons dashicons-download"></span>
                            <?php i18n::e('Get translations'); ?>
                        </a>
                        <?php
                    }
                ?>
                <a href="#" class="smartcat-dropdown-menu-actions_item red smartcat-remove-post-action" post-id="<?php echo $item['id'] ?>">
                    <span class="dashicons dashicons-trash"></span>
                    <?php i18n::e('Remove'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    private function languageCheckbox($languageCode, $postId)
    {
        $postHasTranslatedLang = $this->translationRequest->postHasLocale($postId, $languageCode);
        ?>
        <input
            class="smartcat-language-with-tr"
            type="checkbox"
            value="<?php echo $languageCode ?>"
            language-name="<?php echo $this->get_columns()["language_$languageCode"] ?>"
            post-id="<?php echo $postId ?>"
            in-request="<?php echo $postHasTranslatedLang ? 'true' : 'false' ?>"
            <?php echo $postHasTranslatedLang ? 'checked' : '' ?>
        >
        <?php
    }

    public function prepare_items()
    {
        $this->items = $this->translationRequest->postList();
        $this->_column_headers = [$this->get_columns(), ];
    }

    public function get_columns(): array
    {
        $columns = [
            'name' => 'Post name',
        ];

        $wpmlActiveLocales = sc_wpml()->getActiveLanguages();

        $wpmlActiveLocales = array_filter($wpmlActiveLocales, function ($lang) {
            return $lang['code'] !== $this->sourceLanguage;
        });

        foreach ($wpmlActiveLocales as $lanaguage) {
            $columns["language_{$lanaguage['code']}"] = $lanaguage['translated_name'];
        }

        $columns['actions'] = '';
        return $columns;
    }

    public function single_row($item)
    {
        echo '<tr class="sc-post-row" sc-post-id="' . $item['id'] . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    /**
     * @param mixed $sourceLanguage
     */
    public function setSourceLanguage($sourceLanguage)
    {
        $this->sourceLanguage = $sourceLanguage;
    }

    /**
     * @param mixed $translationRequest
     */
    public function setTranslationRequest($translationRequest)
    {
        $this->translationRequest = $translationRequest;
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            default:
                return 'no value';
        }
    }
}
<?php

namespace Smartcat\Includes\Tables;

use Smartcat\Includes\Plugin\PluginMultilingual as i18n;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class SmartcatLanguages extends \WP_List_Table
{
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
            class="language smartcat-language"
            id="<?php echo $item['language_code'] ?>"
            name="smartcat_languages[]"
            value="<?php echo $item['language_code'] ?>"
        >
        <?php
    }

    public function prepare_items()
    {
        $languages = smartcat_wpml()->getActiveLanguages();
        $this->items = array_filter($languages, function ($l) {
            return $l['active'] !== '1';
        });
        $this->_column_headers = [$this->get_columns(),];
    }

    public function get_columns(): array
    {
        return [
            'name' => 'Language',
            'translate' => 'Translate',
        ];
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            default:
                return 'no value';
        }
    }
}
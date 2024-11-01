<?php

namespace Smartcat\Includes\Tables;

use Smartcat\Includes\Plugin\PluginMultilingual as i18n;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class SmartcatTranslationRequestsTable extends \WP_List_Table
{
    public function column_translationRequest($item)
    {
        ?>
        <span style="display: none;" id="sc-tr-posts-<?php echo $item['id'] ?>" sc-locales="<?php echo json_encode(array_keys($item['languages']['target'])) ?>">
            <?php echo json_encode($item['posts'], JSON_UNESCAPED_UNICODE) ?>
        </span>
        <span style="display: none;" id="sc-tr-locales-<?php echo $item['id'] ?>">
           <?php echo json_encode(array_keys($item['languages']['target'])) ?>
        </span>
        <p>
            <b id="sc-tr-name-<?php echo $item['id'] ?>"><?php echo esc_html($item['name']) ?></b>
        </p>
        <p>
            <?php
                $total = count($item['posts']);
                echo esc_html(implode(', ', array_slice($item['posts'], 0, 2)));
                echo $total >= 3 ? ' and more' : '';
            ?>
        </p>
        <?php
    }

    public function column_smartcatProject($item)
    {
        ?>
        <div class="sc-project-name">
        <?php

        sc_ui()
            ->loader()
            ->isShow(true)
            ->isColored()
            ->render();

        ?>
        <p class="sc-value sc-dn">
            <a href="<?php echo smartcat_api()::getAuthHost(true) ?>/projects/<?php echo $item['project']['id'] ?>" target="_blank">
                <span class="dashicons dashicons-admin-links"></span>
                <span class="value"></span>
            </a>
        </p>
        <?php
        ?>
        </div>
        <?php
    }

    public function column_status($item)
    {
        ?>
        <div class="sc-tr-status" tr-id="<?php echo $item['id'] ?>">
        <?php

        sc_ui()
            ->loader()
            ->isShow(true)
            ->isColored()
            ->render();

        ?>
            <p class="sc-value sc-dn"></p>
        <?php
        ?>
        <a href="#" class="sc-update-tr-progress sc-dn" tr-id="<?php echo $item['id'] ?>"><span class="dashicons dashicons-update"></span></a>
        </div>
        <?php
    }

    public function column_sourceLanguage($item)
    {
        return $item['languages']['source'];
    }

    public function column_targetLanguages($item)
    {
        echo implode(', ', $item['languages']['target']);
    }

    public function column_deadline($item)
    {
        ?>
        <div class="sc-project-deadline">
            <?php
            sc_ui()
                ->loader()
                ->isColored()
                ->isShow(true)
                ->render();

            ?>
            <p class="sc-value sc-dn"></p>
            <?php
            ?>
        </div>
        <?php
    }

    public function column_comment($item)
    {
        echo $item['comment'] ?? '-';
    }

    public function column_action($item)
    {
        ?>
        <div style="display: flex; align-items: center; justify-content: flex-end;">
            <div class="smartcat-spin" style="display: none" id="smartcat-spin-<?php echo $item['id'] ?>">
                <span class="dashicons dashicons-update"></span>
            </div>
        </div>
        <div class="smartcat-dropdown-menu" id="smartcat-actions-<?php echo $item['id'] ?>">
            <span class="smartcat-dropdown-menu-title">Actions <span class="dashicons dashicons-arrow-down"></span></span>
            <div class="smartcat-dropdown-menu-actions">
                <?php
                if (!$item['project']['isError']) {
                    $link = admin_url("/admin.php?page=smartcat-wpml-translation-request");
                    $link = add_query_arg('id', $item['id'], $link);
                    ?>
                    <a href="<?php echo esc_url($link) ?>" class="smartcat-dropdown-menu-actions_item" smartcat-tr-id="<?php echo $item['id'] ?>"><span class="dashicons dashicons-open-folder"></span> <?php i18n::e('Show details') ?></a>
                    <a href="#" class="smartcat-dropdown-menu-actions_item smartcat-get-translations-from-dashboard" smartcat-tr-id="<?php echo $item['id'] ?>"><span class="dashicons dashicons-download"></span> <?php i18n::e('Get translations') ?></a>
                    <?php
                }
                ?>
                <a href="#" style="color: #b32d2e;" class="smartcat-dropdown-menu-actions_item red smartcat-delete-tr-button" smartcat-tr-id="<?php echo $item['id'] ?>"><span class="dashicons dashicons-trash"></span> <?php i18n::e('Delete translation request') ?></a>
            </div>
        </div>
        <?php
    }

    public function prepare_items()
    {
        $limit = 10;

        $orderBy = $_GET['orderby'] ?? 'created_at';
        $order = $_GET['order'] ?? 'DESC';
        $search = $_GET['s'] ?? NULL;

        $perPage = $limit;
        $currentPage = $this->get_pagenum();

        $this->set_pagination_args([
            'total_items' => sc_translation_request_service()->totalRequests(),
            'per_page' => $perPage
        ]);

        $offset = ($currentPage - 1) * $perPage;

        $filter = [
            'source_lang' => $_GET['source_language'] ?? NULL,
            'lang' => $_GET['target_language'] ?? NULL,
            'created_at' => $_GET['created_at'] ?? NULL,
        ];

        $translationRequests = sc_translation_request_service()
            ->list($limit, $offset, $search, $orderBy, $order, $filter);

        $this->items = $translationRequests['items'];
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    public function get_sortable_columns()
    {
        return [
            'smartcatProject' => ['smartcat_project_id', false],
            'comment' => ['comment', false],
        ];
    }

    public function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox">',
            'translationRequest' => i18n::_e('Translation request'),
            'smartcatProject' => i18n::_e('Smartcat project'),
            'status' => i18n::_e('Status'),
            'sourceLanguage' => i18n::_e('Source language'),
            'targetLanguages' => i18n::_e('Target languages'),
            'comment' => i18n::_e('Comment'),
            'deadline' => i18n::_e('Deadline'),
            'action' => ''
        ];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="tr[]" value="%s">', $item['id']);
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            default:
                return 'no value';
        }
    }

    public function single_row($item)
    {
        $cssClass = $item['project']['isError'] ? 'smartcat__error-row' : '';
        echo '<tr class="sc-tr-row ' . $cssClass . '" sc-tr-id="' . $item['id'] . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    function extra_tablenav($which)
    {
        if ($which === 'top') {
            ?>
            <form action="<?php echo admin_url('admin.php'); ?>" method="get" class="smartcat__liner-form alignleft actions bulkactions">
                <input type="hidden" name="page" value="smartcat-wpml-dashboard">
                <div class="smartcat__input-group" style="display:none;">
                    <label for="source-language">Source language</label>
                    <?php
                    $sourceLangFromFilter = $_GET['source_language'] ?? NULL;
                    ?>
                    <select name="source_language" id="source-language" class="ewc-filter-cat">
                        <option value="all">All languages</option>
                        <?php
                        foreach (sc_wpml()->getActiveLanguages() as $language) {
                            ?>
                            <option value="<?php echo $language['code'] ?>" <?php echo $sourceLangFromFilter === $language['code'] ? 'selected' : '' ?>>
                                <?php echo $language['translated_name'] ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="smartcat__input-group">
                    <label for="target-language">Target language</label>
                    <?php
                    $targetLangFromFilter = $_GET['target_language'] ?? NULL;
                    ?>
                    <select name="target_language" id="target-language" class="ewc-filter-cat">
                        <option value="all">All languages</option>
                        <?php
                        foreach (sc_wpml()->getActiveLanguages() as $language) {
                            ?>
                            <option value="<?php echo $language['code'] ?>" <?php echo $targetLangFromFilter === $language['code'] ? 'selected' : '' ?>>
                                <?php echo $language['translated_name'] ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="smartcat__input-group">
                    <label for="created-at">Created at</label>
                    <?php
                    $createdAtFromFilter = $_GET['created_at'] ?? NULL;
                    ?>
                    <input type="date" name="created_at" value="<?php echo $createdAtFromFilter ?>" id="created-at">
                </div>
                <button class="button" style="margin-left: 8px;">Filter</button>
                <a href="<?php echo admin_url("/admin.php?page=smartcat-wpml-dashboard") ?>" class="button" style="margin-left: 8px;">Reset</a>
            </form>
            <?php
        }
    }

    protected function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
            'get_translations' => 'Get translations'
        ];
    }
}
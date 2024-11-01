<?php

namespace Smartcat\Includes\Tables;

use Smartcat\Includes\Plugin\PluginMultilingual as i18n;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class SmartcatLogsTable extends \WP_List_Table
{
    public function column_id($item)
    {
        return $item['id'];
    }

    public function column_type($item)
    {
        $type = ucfirst($item['type']);
        switch ($item['type']) {
            case 'info':
                return "<span style='color: #2e84c9;'><b>$type</b></span>";
            case 'error':
                return "<span style='color: red;'><b>$type</b></span>";
            case 'warning':
                return "<span style='color: orange;'><b>$type</b></span>";
        }
    }

    public function column_message($item)
    {
        return $item['message'];
    }

    public function column_data($item)
    {
        ?>
        <a href="#" class="smartcat-show-event-data">
            <p class="data" style="display: none"><?php echo htmlspecialchars(json_encode($item['data'], JSON_PRETTY_PRINT)) ?></p>
            <span class="dashicons dashicons-index-card"></span>
            <?php i18n::e('Show data') ?>
        </a>
        <?php
    }

    public function column_stackTrace($item)
    {
        if (!empty($item['stackTrace'])) {
            ?>
            <a href="#" class="smartcat-show-event-data">
                <p class="data" style="display: none"><?php echo $item['stackTrace'] ?></p>
                <span class="dashicons dashicons-text-page"></span>
                <?php i18n::e('Show stack trace') ?>
            </a>
            <?php
        } else {
            return 'none';
        }
    }

    public function column_createdAt($item)
    {
        return $item['createdAt'];
    }

    public function prepare_items()
    {
        $type = NULL;
        $query = $_GET['s'] ?? NULL;
        $fromDate = NULL;
        $toDate = NULL;
        $limit = 20;
        $orderBy = $_GET['orderby'] ?? 'created_at';
        $order = $_GET['order'] ?? 'DESC';

        $perPage = $limit;
        $currentPage = $this->get_pagenum();
        $totalItems = smartcat_logger()->totalLogs();

        $offset = ($currentPage - 1) * $perPage;

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page' => $perPage
        ]);


        $this->items = smartcat_logger()->getLogs(
            $type,
            $query,
            $fromDate,
            $toDate,
            $offset,
            $perPage,
            $orderBy,
            $order
        );

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns()
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id' => ['id', false],
            'type' => ['type', false],
            'createdAt' => ['created_at', false],
            'message' => ['message', false],
        ];
    }

    public function get_columns(): array
    {
        return [
            'id' => '№',
            'type' => 'Type',
            'message' => 'Message',
            'data' => 'Data',
            'stackTrace' => 'Stack trace',
            'createdAt' => 'Created At',
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
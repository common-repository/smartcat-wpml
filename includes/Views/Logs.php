<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Tables\SmartcatLogsTable;

class Logs
{
    public function display()
    {
        $table = new SmartcatLogsTable();
        $table->prepare_items();
        $this->searchForm($table);
        $table->display();
        $this->dataBlock();
    }

    private function searchForm($table)
    {
        ?>
        <form action="<?php echo admin_url('admin.php'); ?>" method="get">
            <input type="hidden" name="page" value="smartcat-wpml-logs">
            <?php $table->search_box('Search event(s)', 'smartcat-logs-search-input'); ?>
        </form>
        <?php
    }

    private function dataBlock() {
        ?>
        <pre id="smartcat-logs-dialog"></pre>
        <?php
    }
}
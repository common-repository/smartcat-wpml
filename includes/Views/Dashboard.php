<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Tables\SmartcatTranslationRequestsTable;

class Dashboard
{
    private $components;

    public function __construct()
    {
        $this->components = new Components();
    }

    public function display()
    {
        try {
            $this->components->startWrapper('98%', 'dashboard');
            $table = new SmartcatTranslationRequestsTable();
            $this->components->notices();
            $this->itemsBatchCount();
            $table->prepare_items();
            sc_ui()
                ->row()
                ->css(['margin' => '20px 0'])
                ->flex()
                ->alignItemsCenter()
                ->justifyContentBetween()
                ->body(function () {
                    $this->components->title('Translation requests dashboard');
                    $this->components->helperButton(false, 'dashboard');
                })->render();
            $this->components->tasksInProgress();
            $this->components->itemsInProgress();
            $this->receivingTranslations();
            $this->removingLoader();
            $this->searchForm($table);
            $table->display();
            $this->components->popup();
            $this->components->endWrapper();
        } catch (\Throwable $e) {
            sc_log()->error($e->getMessage(), [
                'stackTrace' => $e->getTraceAsString()
            ]);
        }
    }

    private function searchForm($table)
    {
        ?>
        <form action="<?php echo admin_url('admin.php'); ?>" method="get">
            <input type="hidden" name="page" value="smartcat-wpml-dashboard">
            <?php $table->search_box('Search by post(s)', 'smartcat-logs-search-input'); ?>
        </form>
        <?php
    }

    private function removingLoader()
    {
        sc_ui()
            ->row()
            ->classes('sc-translation-requests-deleting')
            ->css(['display' => 'none'])
            ->flex()
            ->alignItemsCenter()
            ->body(function () {
                sc_ui()
                    ->loader()
                    ->isShow(true)
                    ->isColored()
                    ->render();

                sc_ui()
                    ->text()
                    ->content('Deleting: Trnala asdnj 123')
                    ->classes('sc-translation-request-name')
                    ->render();
            })->render();
    }

    private function receivingTranslations()
    {
        sc_ui()
            ->row()
            ->classes('sc-translation-request-receiving')
            ->css(['display' => 'none'])
            ->flex()
            ->alignItemsCenter()
            ->body(function () {
                sc_ui()
                    ->loader()
                    ->isShow(true)
                    ->isColored()
                    ->render();

                sc_ui()
                    ->text()
                    ->content('Deleting: -')
                    ->classes('sc-translation-request-receiving-status')
                    ->render();
            })->render();
    }

    private function itemsBatchCount()
    {
        ?>
        <input type="hidden" id="sc-number-of-items-to-receive-translations-input" value="<?php echo get_option('sc_number_of_items_to_receive_translations', 5) ?>">
        <?php
    }
}
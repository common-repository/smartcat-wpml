<?php

namespace Smartcat\Includes\Services\Cron;

use Smartcat\Includes\Controllers\Traits\HasTranslationRequest;
use Smartcat\Includes\Services\App\DocumentsService;

class CronHandler
{
    use HasTranslationRequest;

    /** @var DocumentsService */
    private $documentsService;

    public function __construct()
    {
        $this->initTranslationRequest();

        $this->documentsService = new DocumentsService();
    }

    public function init()
    {
        if (!$this->isEnabledGetTranslations()) {
            $this->removeTask(SC_CRON_GET_TRANSLATIONS);
        } else {
            $this->scheduleEvent(SC_CRON_GET_TRANSLATIONS, SC_CRON_EVERY_15_MINUTES);
        }
    }

    public function getTranslationsTask()
    {
        $documents = $this->documentsService->getNonImportedItems(5);

        if (count($documents) === 0) {
            $this->rescheduleTask(SC_CRON_GET_TRANSLATIONS, SC_CRON_EVERY_15_MINUTES);

            $this->documentsService->cancelImportStatus();
            return;
        }

        $this->rescheduleTask(SC_CRON_GET_TRANSLATIONS, SC_CRON_EVERY_MINUTE);

        sc_log('Cron')->info('Started importing translations from Smartcat');

        foreach ($documents as $document) {
            try {
                $this->translationRequest->getTranslationsByPostAndLocale(
                    $document->getTranslationRequestId(),
                    $document->getPostId(),
                    $document->getLang()
                );
            } catch (\Throwable $exception) {
                sc_log('Cron')->error('Failed import of translations from Smartcat', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                    'document' => $document->getStoredData(),
                ]);
            } finally {
                $document = $this->documentsService->findDocumentByPostAndLocale(
                    $document->getPostId(),
                    $document->getLang()
                );

                $document->setIsImported(true);
                $this->documentsService->save($document);
            }
        }

        sc_log('Cron')->info('Importing translations from Smartcat is complete');
    }

    public function intervals(): array
    {
        return [
            SC_CRON_EVERY_MINUTE => [
                'interval' => 60,
                'display' => __('Every minute')
            ],
            SC_CRON_EVERY_2_MINUTE => [
                'interval' => 120,
                'display' => __('Every 2 minute')
            ],
            SC_CRON_EVERY_10_MINUTES => [
                'interval' => 600,
                'display' => __('Every 10 Minutes')
            ],
            SC_CRON_EVERY_15_MINUTES => [
                'interval' => 900,
                'display' => __('Every 15 Minutes')
            ],
            SC_CRON_EVERY_HOUR => [
                'interval' => 3600,
                'display' => __('Every 1 Hour')
            ],
        ];
    }

    public function getTranslationsTaskName(): string
    {
        return $this->getCronTaskName(SC_CRON_GET_TRANSLATIONS);
    }

    private function getCronTaskName(string $name): string
    {
        return "smartcat_wpml_$name";
    }

    private function rescheduleTask(string $name, string $interval)
    {
        if ($this->existsTaskWithInterval($name, $interval)) {
            return;
        }

        $cronTaskName = $this->getCronTaskName($name);

        wp_clear_scheduled_hook($cronTaskName);
        wp_reschedule_event(time(), $interval, $cronTaskName);
    }

    private function scheduleEvent(string $name, string $interval)
    {
        if (!$this->existsTask($name)) {
            $this->rescheduleTask($name, $interval);
        }
    }

    private function existsTask(string $name): bool
    {
        return wp_next_scheduled($this->getCronTaskName($name)) !== false;
    }

    public function existsTaskWithInterval(string $name, string $interval): bool
    {
        $cronTaskName = $this->getCronTaskName($name);

        foreach (_get_cron_array() as $taskData) {
            if (isset($taskData[$cronTaskName])) {
                foreach ($taskData[$cronTaskName] as $task) {
                    if ($task['schedule'] === $interval) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function removeTask(string $name)
    {
        wp_clear_scheduled_hook($this->getCronTaskName($name));
    }

    public function isEnabledGetTranslations(): bool
    {
        return sc_check_option('sc_automatically_get_translations');
    }
}
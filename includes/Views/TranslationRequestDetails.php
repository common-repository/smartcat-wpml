<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Services\App\Models\TranslationRequest;
use Smartcat\Includes\Services\Tools\Notice;
use Smartcat\Includes\Tables\SmartcatTranslationRequestPosts;

class TranslationRequestDetails
{
    private $components;
    /** @var TranslationRequest */
    private $tr;

    public function __construct()
    {
        $this->components = new Components();
        $this->tr = sc_translation_request_service()->find($_GET['id'] ?? null);
    }

    public function display()
    {
        $this->components->startWrapper('98%', 'tr-details');

        if ($this->tr->exists()) {
            $this->components->hiddenInputs(
                $this->tr->sourceLocale(), NULL,
                $this->tr->id()
            );

            $project = smartcat_hub_client()->getProject(
                $this->tr->smartcatProjectId(),
                $this->tr->sourceLocale()
            );

            if (is_wp_error($project)) {
                $project->showError();
                return;
            }

            sc_ui()
                ->row()
                ->css(['margin' => '20px 0'])
                ->flex()
                ->alignItemsCenter()
                ->justifyContentBetween()
                ->body(function () {
                    $this->components->title("{$this->tr->name()} - details");
                    $this->components->helperButton(false, 'translation-request');
                })->render();

            sc_ui()
                ->row()
                ->css(['gap' => '10px'])
                ->flex()
                ->alignItemsCenter()
                ->body(function () use ($project) {
                    $this->components->projectLink(
                        $this->tr->smartcatProjectId(),
                        $project->getName(),
                        'max-content'
                    );

                    $this->components->updateAllPostsButton();
                })->render();


            $this->components->projectDeadline($project->getDeadline());

            sc_ui()
                ->text()
                ->content('Add / Remove languages')
                ->render();
            sc_ui()
                ->text()
                ->css(['display' => 'block', 'font-size' => '12px'])
                ->content('You can add or remove languages for all documents in the current translation request.')
                ->render();
            sc_ui()
                ->row()
                ->css(['column-gap' => '10px', 'flex-wrap' => 'wrap'])
                ->flex()
                ->alignItemsCenter()
                ->body(function () {
                    $this->components->wpmlLanguagesList([$this->tr->sourceLocale()]);
                    $this->components->addAndRemoveLanguagesButtons();
                })->render();

            $this->components->sendingLoader();

            $table = new SmartcatTranslationRequestPosts();

            $table->setSourceLanguage($this->tr->sourceLocale());

            $table->setTranslationRequest($this->tr);

            $table->prepare_items();
            $this->components->notices();
            $table->display();
            $this->components->popup();
        } else {
            Notice::notice('error', 'Translation request not found.');
        }
        $this->components->endWrapper();
    }
}
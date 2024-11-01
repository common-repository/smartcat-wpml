<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Services\API\SmartcatClient;
use Smartcat\Includes\Plugin\PluginMultilingual as i18n;
use Smartcat\Includes\Services\App\Models\TranslationRequest;

class Metabox
{
    private $components;
    private $postId;
    private $sourceLanguageName;
    private $sourceLanguageCode;
    private $hasTranslationRequest;
    private $translationRequestId = NULL;
    /** @var TranslationRequest|null  */
    private $tr;
    private $project;

    public function __construct($postId)
    {
        $this->components = new Components();
        $this->postId = $postId;

        $this->hasTranslationRequest = sc_app_helpers()->postInTranslationRequest($postId);

        $this->sourceLanguageName = sc_wpml()->getPostLanguageName($postId);
        $this->sourceLanguageCode = sc_wpml()->getPostLanguageCode($postId);

        if ($this->hasTranslationRequest) {
            $this->translationRequestId = sc_app_helpers()->getPostTranslationRequest($postId);

            $this->tr = sc_translation_request_service()->find($this->translationRequestId);

            $this->project = smartcat_hub_client()->getProject(
                $this->tr->smartcatProjectId(),
                $this->tr->sourceLocale()
            );

            sc_translation_request_service()
                ->updateDocumentsProgress($this->translationRequestId, $this->project);
        }
    }

    public function display()
    {
        if (empty($this->sourceLanguageCode)) {
            echo 'Finish creating an article to send for translation';
            return;
        }

        $this->components->helperButton(false, 'metabox');

        if (SmartcatClient::isAuthorized()) {
            $this->components->hiddenInputs($this->sourceLanguageCode, $this->postId, $this->translationRequestId);
            $this->components->sourceLanguage($this->sourceLanguageName);

            if ($this->hasTranslationRequest) {
                if (is_wp_error($this->project)) {
                    $this->errorProject();
                    return;
                }
                $this->components->updateInfoButton();
                $this->components->newTranslatableLanguages($this->postId);
                $this->components->projectDeadline($this->project->getDeadline() ?? NULL);
                $this->components->sendingLoader();
                $this->components->getTranslationsLoader();
                $this->components->projectLink($this->project->getId(), $this->project->getName());
                $this->components->startButtonGroup();
                $this->components->updateSourceButton();
                $this->components->getTranslationsButton();
                $this->components->skipPackagesImport();
                $this->components->endButtonGroup();
            } else {
                $this->components->newTargetLanguages();
                //$this->components->languagesTable();
                $this->components->workflowStages();
                $this->components->projectsSelector($this->sourceLanguageCode);
                $this->components->deadlineInput();
                $this->components->commentInput();
                $this->components->sendingLoader();
                $this->components->sendToSmartcatButton();
            }
            $this->components->notices();
            $this->components->popup();
            $this->components->loadingMetabox();
        } else {
            $this->unauthorizedNotice();
        }
    }

    private function errorProject()
    {
        ?>
        <p style="color:red;"><?php i18n::e('This post is found in a translation request whose project could not be uploaded. Your account may not have access. Authorize your account and try again or delete the translation request.') ?></p>
        <?php
    }

    private function unauthorizedNotice()
    {

        ?>
        <p>
            Log in to Smartcat in <a href="<?php echo admin_url("/admin.php?page=smartcat-wpml") ?>">settings</a>.
        </p>
        <?php
    }
}
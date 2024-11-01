<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Plugin\PluginMultilingual as i18n;
use Smartcat\Includes\Tables\SmartcatLanguages;
use Smartcat\Includes\Tables\SmartcatLanguagesWithTranslations;

class Components
{
    public function  newTargetLanguages()
    {
        $languages = smartcat_wpml()->getActiveLanguages();
        $items = array_filter($languages, function ($l) {
            return $l['active'] !== '1';
        });

        echo '<div class="sc-languages">';
        foreach($items as $item) {
            ?>
            <div class="sc-languages__item">
                <div class="left-side">
                    <input
                        type="checkbox"
                        class="language smartcat-language sc-languages__item--checkbox"
                        id="<?php echo $item['language_code'] ?>"
                        name="smartcat_languages[]"
                        value="<?php echo $item['language_code'] ?>"
                    >
                    <label
                        for="<?php echo $item['language_code'] ?>"
                        class="sc-languages__item--label"
                    >
                        <?php echo $item['translated_name'] ?>
                    </label>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    public function newTranslatableLanguages($postID)
    {
        $languages = sc_wpml()->getActiveLanguages();
        $postLanguage = sc_wpml()->getPostLanguageCode($postID);

        $languages = array_filter($languages, function ($l) use ($postLanguage) {
            return $l['code'] !== $postLanguage;
        });

        $items = array_filter($languages, function ($l) {
            return $l['active'] !== '1';
        });

        $documents = sc_app_helpers()->postDocuments($postID);

        $items = array_map(function ($l) use ($documents, $postID) {
            $document = array_filter($documents, function ($d) use ($l) {
                return $d->getLang() === $l['language_code'];
            });

            $document = array_shift($document);

            $l['in_translation_request'] = !empty($document);
            $l['has_translated_post'] = !is_null($document) && !is_null($document->getPostId());
            $l['translated_post_id'] = !is_null($document) ? $document->getTranslatedPostId() :  NULL;
            $l['smartcat_document_id'] = !is_null($document) ? $document->getSmartcatDocumentId() : NULL;
            $l['translation_progress'] = !is_null($document) ? $document->getTranslationProgress() : NULL;
            $l['post_id'] = $postID;

            return $l;
        }, $languages);

        echo '<div class="sc-languages">';
        foreach($items as $item) {
        ?>
            <div class="sc-languages__item">
                <div class="left-side">
                    <input
                        type="checkbox"
                        in-request="<?php echo $item['in_translation_request'] ? 'true' : 'false' ?>"
                        language-name="<?php echo $item['translated_name'] ?>"
                        post-id="<?php echo $item['post_id'] ?>"
                        class="language smartcat-language-with-tr sc-languages__item--checkbox"
                        id="<?php echo $item['language_code'] ?>"
                        value="<?php echo $item['language_code'] ?>"
                        <?php echo $item['in_translation_request'] ? 'checked' : '' ?>
                    >
                    <label
                        for="<?php echo $item['language_code'] ?>"
                        class="sc-languages__item--label"
                    >
                        <?php echo $item['translated_name'] ?>
                    </label>
                </div>
                <div class="right-side">
                    <?php
                        if ($item['in_translation_request']) {
                            ?>
                            <span class="sc-badge"><?php echo $item['translation_progress']; ?>%</span>
                            <div class="smartcat-dropdown-menu">
                                <span class="dashicons dashicons-ellipsis"></span>
                                <div class="smartcat-dropdown-menu-actions">
                                    <a
                                        href="<?php echo $this->getSmartcatDocumentLink($item['smartcat_document_id']) ?>"
                                        class="smartcat-dropdown-menu-actions_item"
                                        target="_blank"
                                    >
                                        <span class="dashicons dashicons-translation"></span>
                                        <?php i18n::e('Edit in Smartcat'); ?>
                                    </a>
                                    <a
                                            href="<?php echo $item['has_translated_post'] ? get_edit_post_link($item['translated_post_id']) : '#' ?>"
                                            class="smartcat-dropdown-menu-actions_item <?php echo !$item['translated_post_id'] ? 'disabled' : '' ?>"
                                            target="_blank"
                                    >
                                        <span class="dashicons dashicons-wordpress-alt"></span>
                                        <?php i18n::e('Edit in Wordpress'); ?></a>
                                </div>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        <?php
        }
        echo '</div>';
    }

    private function getSmartcatDocumentLink($documentId): string
    {
        $split = explode('_', $documentId);
        $id = $split[0];
        $lang = $split[1];
        return smartcat_api()::getAuthHost(true) . "/editor?documentId=$id&languageId=$lang";
    }

    public function getTranslationsButton()
    {
        ?>
        <button id="smartcat-get-translations" class="button button-secondary smartcat-button">
            <span class="dashicons dashicons-update loader"></span>
            <span class="dashicons dashicons-download"></span>
            <?php i18n::e('Get from Smartcat'); ?>
        </button>
        <?php
    }

    public function projectLink($projectId, $projectName, $width = '100%')
    {
        ?>
        <input type="hidden" id="smartcat-project-id" value="<?php echo $projectId ?>" >
        <a target="_blank" href="<?php echo smartcat_api()::getAuthHost(true) ?>/projects/<?php echo $projectId ?>/files" class="button smartcat-button" style="display: block; width: <?php echo $width?>;margin-bottom: 10px; !important;">
            <span class="dashicons dashicons-category"></span>
            <?php i18n::e('Open Smartcat project'); ?>
        </a>
        <?php
    }

    public function projectDeadline($deadline)
    {
        ?>
        <p><?php i18n::e('Deadline'); ?>: <i><?php
                $deadline = isset($deadline) ? new \DateTime($deadline) : NULL;
                echo !empty($deadline) ? $deadline->format('Y/m/d H:i:s') : i18n::_e('Not set')
                ?></i></p>
        <?php
    }

    public function commentInput()
    {
        ?>
        <label class="wp-caption-text" style="display: block;margin-top: 10px;" for="smartcat-comment-input">
            <?php i18n::e('Comment'); ?>
        </label>
        <textarea
            placeholder="<?php i18n::e('Example: Read the following style guide before translat...'); ?>"
            id="smartcat-comment-input"
            style="display: block; margin: 10px 0; width: 100%;"
        ></textarea>
        <?php
    }

    public function sendToSmartcatButton()
    {
        ?>
        <button id="send-to-smartcat-button" style="margin-top: 10px; width: 100%;" class="button button-primary smartcat-button" disabled>
            <span class="dashicons dashicons-update loader"></span>
            <span class="dashicons dashicons-upload"></span>
            <?php i18n::e('Send to Smartcat'); ?>
        </button>
        <?php
    }

    public function updateSourceButton()
    {
        ?>
        <button id="update-source-content-button" class="button button-primary smartcat-button">
            <span class="dashicons dashicons-update loader"></span>
            <span class="dashicons dashicons-upload"></span>
            <?php i18n::e('Send to Smartcat'); ?>
        </button>
        <?php
    }

    public function updateInfoButton()
    {
        ?>
        <div style="display: flex; justify-content: flex-end;">
            <button id="sc-update-post-info" class="button smartcat-button">
                <span class="dashicons dashicons-update" style="margin-right: 0;"></span>
            </button>
        </div>
        <?php
    }

    public function helperButton(bool $onlyIcon = false, string $refer = '')
    {
        ?>
        <a
            target="_blank"
            class="button smartcat-danger-button smartcat-helper-button"
            href="<?php echo admin_url("/admin.php?page=smartcat-wpml-faq&refer=$refer") ?>"
            style="<?php echo !$onlyIcon ? 'margin-bottom: 10px' : 'width: fit-content' ?>"
        >
            <span class="dashicons dashicons-editor-help"></span>
            <?php if (!$onlyIcon): ?>
                <span><?php i18n::e('Smartcat Helper'); ?></span>
            <?php endif; ?>
        </a>
        <?php
    }

    public function deadlineInput()
    {
        ?>
        <label class="wp-caption-text" style="display: block;margin-top: 10px;" for="smartcat-deadline-input">
            <?php i18n::e('Deadline'); ?>
        </label>
        <input
            type="datetime-local"
            id="smartcat-deadline-input"
            style="display: block; margin: 10px 0;"
        >
        <?php
    }

    public function newProjectsSelector($sourceLocale = null)
    {
        $projects = smartcat_api()->getProjectsList();

        ?>
        <div class="sc-dropdown" id="sc-projects-selector">
           <button class="sc-dropdown__selector" sc-selected-project-id="new" sc-deadline="">
               <span class="name"><?php i18n::e('New project'); ?></span>
               <span class="dashicons dashicons-arrow-down"></span>
           </button>
            <div class="sc-dropdown__items">
                <input type="text" placeholder="Project name" class="sc-dropdown__search">
                <div class="sc-dropdown__items--list">
                    <button class="sc-dropdown__items--item" sc-project-id="new" sc-deadline="">
                        <b>New project</b>
                    </button>
                    <?php
                        foreach ($projects as $project) {
                            if ($project->getSourceLocale() === sc_locale()->map($sourceLocale)) {
                            ?>
                                <button
                                    class="sc-dropdown__items--item"
                                    sc-project-id="<?php echo $project->getId() ?>"
                                    sc-deadline="<?php echo $project->getDeadline() ?>"
                                >
                                    <?php echo $project->getName() ?>
                                </button>
                            <?php
                            }
                        }
                    ?>
                </div>
                <div class="sc-dropdown__items__loader" style="display: none">
                    <div class="sc-loader color">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function projectsSelector($sourceLanguageCode)
    {
        ?>
        <label class="wp-caption-text" style="display: block; margin-top: 10px;" for="">
            <?php i18n::e('Smartcat project'); ?>
        </label>
        <?php
        $this->newProjectsSelector($sourceLanguageCode);
    }

    public function workflowStages()
    {
        ?>
            <label class="wp-caption-text" style="display: block; margin-top: 10px; margin-bottom: 10px;" for="">
                <?php i18n::e('Workflow stages'); ?>
            </label>
            <select id="sc-workflow-stage">
                <option value="mt" selected>
                    <?php i18n::e('AI translation'); ?>
                </option>
                <option value="mt-postediting">
                    <?php i18n::e('AI translation + human review'); ?>
                </option>
                <option value="manual">
                    <?php i18n::e('Manual translation'); ?>
                </option>
            </select>
        <?php
    }

    public function languagesTableWithTranslations($postId)
    {
        ?>
        <label class="wp-caption-text" for="source_language" style="display: block; margin: 10px 0;">
            <?php i18n::e('Target languages'); ?>
        </label>
        <?php
        $table = new SmartcatLanguagesWithTranslations();
        $table->setPostId($postId);
        $table->prepare_items();
        $table->display();
    }

    public function languagesTable()
    {
        ?>
        <label class="wp-caption-text" for="source_language" style="display: block; margin: 10px 0;">
            <?php i18n::e('Target languages'); ?>
        </label>
        <?php
        $table = new SmartcatLanguages();
        $table->prepare_items();
        $table->display();
    }

    public function hiddenInputs($sourceLanguageCode, $postId = NULL, $translationRequestId = NULL)
    {
        ?>
        <input type="hidden" id="smartcat-post-id" value="<?php echo $postId ?>">
        <input type="hidden" id="smartcat-source-language" value="<?php echo $sourceLanguageCode ?>">
        <input type="hidden" id="smartcat-tr-id" value="<?php echo $translationRequestId ?>">
        <?php
    }

    public function sourceLanguage(string $sourceLanguageName)
    {
        ?>
        <label class="wp-caption-text" for="source_language">
            <?php i18n::e('Source language'); ?>: <b><?php echo $sourceLanguageName ?></b>
        </label>
        <?php
    }

    public function authForm()
    {
        ?>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="smartcat_log_in">
            <button type="submit" style="margin-top: 10px;" id="log-in-button" class="button button-secondary"><?php i18n::e('Log in with Smartcat'); ?></button>
        </form>
        <?php
    }

    public function notices()
    {
        ?>
        <p id="smarcat-notice-warn" class="notice notice-warning" style="line-break: anywhere;display: none; margin: 10px 0; padding: 5px 10px;"></p>
        <p id="smarcat-notice-success" class="notice notice-success" style="line-break: anywhere;display: none; margin: 10px 0; padding: 5px 10px;"></p>
        <p id="smarcat-notice-info" class="notice notice-info" style="line-break: anywhere;display: none; margin: 10px 0; padding: 5px 10px;"></p>
        <p id="smarcat-notice-error" class="notice notice-error" style="line-break: anywhere;display: none; margin: 10px 0; padding: 5px 10px;"></p>
        <?php
    }

    public function popup()
    {
        ?>
        <div class="smartcat__popup--wrapper">
            <div class="smartcat__popup--body">
                <div class="data" style="display: none"></div>
                <p class="smartcat__popup--title"></p>
                <div class="smartcat__popup--buttons">
                    <button class="button cancel"><?php i18n::e('Cancel'); ?></button>
                    <button class="button button-primary confirm smartcat__button">
                        <span class="dashicons dashicons-update"></span>
                        <?php i18n::e('Yes'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    public function startWrapper($width = '100%', $classes = '')
    {
        echo '<div class="smartcat__wrapper ' . $classes . ' " style="width: ' . $width . ';">';
    }

    public function endWrapper()
    {
        echo '</div>';
    }

    public function startButtonGroup()
    {
        ?>
        <div class="smartcat__button--group">
        <?php
    }

    public function endButtonGroup()
    {
        ?>
        </div>
        <?php
    }

    public function title(string $text = '')
    {
        ?>
        <h2 class="smartcat__title" style="margin: 0;"><?php i18n::e($text) ?></h2>
        <?php
    }

    public function loadingMetabox()
    {
        ?>
        <div class="sc-metabox-loading" style="display: none;">
            <span>Updating</span>
        </div>
        <?php
    }

    public function sendingLoader()
    {
        sc_ui()
            ->row()
            ->classes('sc-sending-posts-to-smartcat')
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
                    ->content('Sending: ')
                    ->classes('sc-sending-post-name')
                    ->render();
            })->render();
    }

    public function getTranslationsLoader()
    {
        sc_ui()
            ->row()
            ->classes('sc-get-translations-progress')
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
                    ->content('Importing: ')
                    ->classes('sc-get-translations-name')
                    ->render();
            })->render();
    }

    public function wpmlLanguagesList(array $ignore = [])
    {
        $languages = sc_wpml()->getActiveLanguages();
        $languages = array_filter($languages, function ($l) use ($ignore) {
            return !in_array($l['code'], $ignore);
        });
        ?>
            <div class="sc-wpml-languages">
                <?php foreach ($languages as $language): ?>
                    <div class="sc-wpml-languages__item">
                        <img
                            src="<?php echo $language['country_flag_url'] ?>"
                            alt="<?php echo $language['translated_name'] ?>"
                        >
                        <input type="checkbox" class="sc-wpml-language" value="<?php echo $language['code'] ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php
    }

    public function addAndRemoveLanguagesButtons()
    {
        ?>
        <div class="sc-add-remove-languages-buttons">
            <button
                class="button"
                id="sc-add-languages-to-translation-request"
            ><?php i18n::e('Add'); ?></button>
            <button
                class="button smartcat-danger-button"
                id="sc-remove-languages-from-translation-request"
            ><?php i18n::e('Remove'); ?></button>
        </div>
        <?php
    }

    public function updateAllPostsButton()
    {
        ?>
        <button id="sc-update-posts-in-smartcat-button" style="margin-bottom: 10px;" class="button button-primary smartcat-button">
            <span class="dashicons dashicons-update loader"></span>
            <span class="dashicons dashicons-upload"></span>
            <?php i18n::e('Sync all posts'); ?>
        </button>
        <?php
    }

    public function skipPackagesImport()
    {
        ?>
        <div class="sc-skip-packages-import" title="<?php i18n::e('This option will skip importing WPML package strings into WordPress. May be needed if you only need to import custom fields.'); ?>">
            <input type="checkbox" id="sc-skip-packages-import">
            <label for="sc-skip-packages-import"><?php i18n::e('Skip packages content import'); ?></label>
        </div>
        <?php
    }

    public function itemsInProgress()
    {
        ?>
        <div id="sc-items-in-progress" style="margin-bottom: 20px;"></div>
        <?php
    }

    public function tasksInProgress()
    {
        ?>
        <h3 id="sc-tasks-in-progress" style="margin-bottom: 20px;display: none;">Task in progress: <span class="value"></span></h3>
        <?php
    }
}
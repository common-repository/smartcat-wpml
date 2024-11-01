<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Services\Tools\Notice;

class CreateTranslationRequest
{
    private $components;
    private $postIds = [];
    private $posts = [];
    private $sourceLanguageCode;
    private $sourceLanguageName;
    private $postIdsWithoutTR;

    public function __construct()
    {
        $this->components = new Components();

        if (smartcat_check_json_from_param('posts')) {
            $this->postIds = json_decode($_GET['posts'], true);

            foreach ($this->postIds as $postId) {
                $post = get_post($postId);
                if (!empty($post)) {
                    $this->posts[] = $post;
                }
            }

            $this->postIdsWithoutTR = array_values(array_filter($this->postIds, function ($p) {
                return !sc_app_helpers()->postInTranslationRequest($p);
            }));

            $postId = $this->postIds[0];

            $this->sourceLanguageCode = sc_wpml()->getPostLanguageCode($postId);
            $this->sourceLanguageName = sc_wpml()->getPostLanguageName($postId);
        }
    }

    public function display()
    {
        $this->components->startWrapper('50%', 'create-tr');
        if (smartcat_check_json_from_param('posts')) {
            $this->components->hiddenInputs($this->sourceLanguageCode, json_encode($this->postIdsWithoutTR));
            $this->components->title('Selected posts');
            $this->selectedPosts();
            $this->components->title('Settings');
            $this->components->sourceLanguage($this->sourceLanguageName);
            $this->components->languagesTable();
            $this->components->workflowStages();
            $this->components->projectsSelector($this->sourceLanguageCode);
            $this->components->deadlineInput();
            $this->components->commentInput();
            $this->components->sendingLoader();
            if (count($this->postIdsWithoutTR) === 0) {
                Notice::warn('There are no matching posts in the current configuration. You cannot continue.');
            } else {
                $this->components->sendToSmartcatButton();
            }
            $this->components->notices();
            $this->components->popup();
        } else {
            Notice::error(__('Select posts to translate', 'smartcat-wpml'), 'smartcat-mt-20');
        }
        $this->components->endWrapper();
    }

    private function selectedPosts()
    {
        ?>
        <ul class="smartcat__list">
            <?php
            foreach ($this->posts as $post) {
                ?>
                <li>
                    <a href="<?php echo get_edit_post_link($post->ID) ?>" sc-post-id="<?php echo $post->ID ?>" target="_blank">
                        <?php echo $post->post_title ?>
                    </a>
                    <?php
                    if (sc_app_helpers()->postInTranslationRequest($post->ID)) {
                        $link = admin_url("/admin.php?page=smartcat-wpml-translation-request");
                        $link = add_query_arg('id', sc_app_helpers()->getPostTranslationRequest($post->ID), $link);
                        ?>
                        <span style="color: #ba6643;display: block;margin-bottom: 10px;margin-top: 5px; font-size: 12px;">
                    <i>- This post is already in one of the <a href="<?php echo $link ?>" target="_blank">translation requests</a>. This post will be skipped.</i>
                </span>
                        <?php
                    }
                    ?>
                </li>
                <?php
            }
            ?>
        </ul>
        <?php
    }
}
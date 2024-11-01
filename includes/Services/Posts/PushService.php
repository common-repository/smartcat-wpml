<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Requests\PushPostsRequest;
use Smartcat\Includes\Services\Elementor\Document;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\MetadataServiceInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PushService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var MetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabase;

    public function __construct(
        WpmlInterface            $wpmlService,
        MetadataServiceInterface $metadataService,
        PostsDatabaseInterface   $postsDatabase
    )
    {
        $this->wpmlService = $wpmlService;
        $this->metadataService = $metadataService;
        $this->postsDatabase = $postsDatabase;
    }

    public function push(PushPostsRequest $request)
    {
        foreach ($request->getPosts() as $post) {
            $targetPostId = $this->wpmlService->getTargetElementId(
                $post['originalId'],
                $post['targetLang'],
                "post_{$post['type']}"
            );

            if (!empty($targetPostId)) {
                $this->updatePost($targetPostId, $post);
            } else {
                $this->addPost($post, $request->getLang());
            }
        }
    }

    private function updatePost(int $postId, array $postData)
    {
        $originalPost = get_post($postData['originalId']);

        if (is_elementor_installed()) {
            $mayBeElementorOriginalPost = new Document($originalPost->ID);

            if ($mayBeElementorOriginalPost->isBuiltWithElementor()) {
                // updating target post data of original post
                (new Document($postId))->save(
                    $mayBeElementorOriginalPost->getElements(true)
                );

                $mayBeElementorTargetPost = new Document($postId);
                $mayBeElementorTargetPost->updateElementsData($postData['blocks']);
            } else {
                // FIXME: duplicated code
                $content = BlocksService::applyTranslatedBlocks(
                    $originalPost->post_content,
                    $postData['blocks']
                );

                $this->postsDatabase->updatePostContent($postId, $content);
            }
        } else {
            $blocks = $postData['blocks'];

            if (sc_built_with_bakery($originalPost->ID)) {
                $blocks = array_map(function ($block) {
                    $block['content'] = sc_bakery_builder()->encode($block['content']);
                    return $block;
                }, $blocks);
            }

            $content = BlocksService::applyTranslatedBlocks(
                $originalPost->post_content,
                $blocks
            );

            $this->postsDatabase->updatePostContent($postId, $content);
        }

        wp_update_post([
            'ID' => $postId,
            'post_title' => $postData['title'],
            'post_status' => $postData['postStatus'] ?? 'draft'
        ]);

        $this->metadataService->addMetaDataToPost($postId, $postData['metadata']);
    }

    private function addPost(array $postData, string $sourceLang)
    {
        $originalPost = get_post($postData['originalId']);

        $id = smartcat_wpml()->makeDuplicate($postData['originalId'], $postData['targetLang']);

        if (is_elementor_installed()) {
            $mayBeElementorPost = new Document($originalPost->ID);

            if ($mayBeElementorPost->isBuiltWithElementor()) {
                // updating target post data of original post
                (new Document($id))->save(
                    $mayBeElementorPost->getElements(true)
                );

                $targetPost = new Document($id);
                $targetPost->updateElementsData($postData['blocks']);
            } else {
                // FIXME: duplicated code

                $postData['content'] = BlocksService::applyTranslatedBlocks(
                    $originalPost->post_content,
                    $postData['blocks']
                );

                $this->postsDatabase->updatePostContent($id, $postData['content']);
            }
        } else {
            $blocks = $postData['blocks'];

            if (sc_built_with_bakery($originalPost->ID)) {
                $blocks = array_map(function ($block) {
                    $block['content'] = sc_bakery_builder()->encode($block['content']);
                    return $block;
                }, $blocks);
            }

            $postData['content'] = BlocksService::applyTranslatedBlocks(
                $originalPost->post_content,
                $blocks
            );

            $this->postsDatabase->updatePostContent($id, $postData['content']);
        }

        wp_update_post([
            'ID' => $id,
            'post_title' => $postData['title'],
            'post_status' => $postData['postStatus'] ?? 'draft'
        ]);

        $this->metadataService->addMetaDataToPost($id, $postData['metadata']);
    }

    private function handleCustomFields(int $postId, array $postData)
    {
        $this->metadataService->updateMetaDataByOriginalPost($postData['originalId'], $postId);
        $this->metadataService->addMetaDataToPost($postId, $postData['metadata']);
    }
}
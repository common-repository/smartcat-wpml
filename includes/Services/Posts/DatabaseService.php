<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;

class DatabaseService implements PostsDatabaseInterface
{
    private $defaultArgs = [
        'post_type' => ['post', 'page'],
        'numberposts' => -1,
        'suppress_filters' => false,
    ];

    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function getPosts(array $args = []): array
    {
        $args = empty($args) ? $this->defaultArgs : $args;
        return get_posts($args);
    }

    /**
     * @param int $postId
     * @param string $originalPostName
     * @return string
     * @throws \Exception
     */
    public function normalizePostName(int $postId, string $originalPostName): string
    {
        $permalink = get_permalink($postId);
        $linkComponents = parse_url($permalink);
        $uri = $linkComponents['path'] ?? NULL;
        if (empty($uri)) {
            throw new \Exception('Key "path" not found');
        }
        $uri = trim($uri, '/');
        $explodeUri = explode('/', $uri);
        if (count($explodeUri) > 1) {
            array_pop($explodeUri);
            $postName = str_replace('/', '-', implode('-', $explodeUri));
            $postName .= '--' . $originalPostName;
        } else {
            $postName = str_replace('/', '-', $uri);
        }
        return urldecode($postName);
    }

    public function getCategoriesIds(int $postId): array
    {
        $categories = get_the_category($postId);
        return array_unique(array_map(function ($category) {
            return $category->cat_ID;
        }, $categories));
    }


    public function insertPost(array $postData): int
    {
        return wp_insert_post([
            'post_title' => !is_string($postData['title']) ? '' : $postData['title'],
            // 'post_content' => $postData['content'],
            'post_category' => $postData['categories'],
            'post_type' => $postData['type'],
            'post_status' => $postData['postStatus'],
        ]);
    }

    public function updatePostContent(int $postId, $content)
    {
        $this->wpdb->update($this->wpdb->posts, [
            'post_content' => $content
        ], ['ID' => $postId]);
    }
}
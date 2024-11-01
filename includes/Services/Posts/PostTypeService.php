<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Services\Interfaces\PostTypeInterface;

class PostTypeService implements PostTypeInterface
{
    private $wpml;

    public function __construct()
    {
        global $sitepress_settings;
        $this->wpml = $sitepress_settings;
    }

    public function getTranslatableTypes(): array
    {
        $postTypes = get_post_types(['public' => true,], 'objects');

        $wpmlPostTypes = $this->getWpmlPostTypes();

        $filteredTypes = array_filter($postTypes, function ($t) use ($wpmlPostTypes) {
            return !in_array($t->name, SMARTCAT_IGNORE_POST_TYPES)
                && (isset($wpmlPostTypes[$t->name]) && (int)$wpmlPostTypes[$t->name] !== WPML_CONTENT_TYPE_DONT_TRANSLATE);
        });

        $mappedTypes = array_map(function ($t) {
            return $t->name;
        }, $filteredTypes);

        return array_values($mappedTypes);
    }

    public function getWpmlPostTypes(): array
    {
        return $this->wpml['custom_posts_sync_option'] ?? [];
    }
}

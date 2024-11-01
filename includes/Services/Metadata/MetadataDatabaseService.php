<?php

namespace Smartcat\Includes\Services\Metadata;

use Smartcat\Includes\Services\Interfaces\MetadataDatabaseInterface;

class MetadataDatabaseService implements MetadataDatabaseInterface
{
    private $wpdb;
    private $customFieldService;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->customFieldService = new CustomField();
    }

    public function getPostMetadata(int $postId, string $key = '', bool $isSingle = false)
    {
        return get_post_meta($postId, $key, $isSingle);
    }

    public function getCustomFieldKeys(): array
    {
        return $this->wpdb->get_col(
            $this->getNotSystemCustomFieldsQuery()
        );
    }

    public function getCustomFields($isLocalizable = true): array
    {
        $fields = $this->getCustomFieldKeys();

        if ($isLocalizable) {
            $data = [];
            foreach ($fields as $field) {
                if ($this->customFieldService->isLocalizable($field)) {
                    $data[] = $field;
                }
            }
            return $data;
        }

        return $fields;
    }

    private function getNotSystemCustomFieldsQuery(): string
    {
        $excludedKeys = \WPML_Post_Custom_Field_Setting_Keys::get_excluded_keys();

        $excludedKeys = array_map(function ($k) {
            return "'$k'";
        }, $excludedKeys);

        $requiredSystemFields = array_map(function ($f) {
            return "'$f'";
        }, SMARTCAT_REQUIRED_SYSTEM_POST_METADATA);

        return "SELECT SQL_CALC_FOUND_ROWS DISTINCT meta_key 
                FROM {$this->wpdb->postmeta} 
                WHERE 1 = 1  
                AND meta_key NOT IN(" . implode(', ', $excludedKeys) . ") 
                AND (meta_key NOT LIKE '\_%' OR meta_key IN (" . implode(', ', $requiredSystemFields) . "))";
    }
}
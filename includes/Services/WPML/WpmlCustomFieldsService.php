<?php

namespace Smartcat\Includes\Services\WPML;

use Smartcat\Includes\Services\WPML\Models\WpmlContentItem;
use WPML\TM\Settings\Repository;

class WpmlCustomFieldsService
{
    /**
     * @param WpmlContentItem[] $items
     * @param $postId
     * @param $originalPostId
     * @return void
     */
    public function save(array $items, $postId, $originalPostId)
    {
        $fields = Repository::getCustomFields();

        $fieldNames = [];

        foreach ($fields as $fieldName => $val) {

            if ((string)$fieldName === '') {
                continue;
            }

            foreach ($items as $item) {
                $fieldIdString = $this->getCustomFieldId($fieldName, $item);

                if ($fieldIdString) {
                    $fieldNames[$fieldName] = $fieldNames[$fieldName] ?? [];

                    $fieldTranslation = false;

                    foreach ($items as $v) {
                        if ("field-$fieldIdString" === $v->getKey()) {
                            $fieldTranslation = $v->getTranslatedData();
                        }

                        if ("field-$fieldIdString-type" === $v->getKey()) {
                            $fieldType = $v->getSourceData();
                            break;
                        }
                    }

                    if ($fieldTranslation !== false && isset($fieldType) && $fieldType === 'custom_field') {
                        $fieldIdString = $this->removeFieldNameFromStart($fieldName, $fieldIdString);

                        $metaKeys = wpml_collect(explode('-', $fieldIdString))
                            ->map(['WPML_TM_Field_Type_Encoding', 'decode_hyphen'])
                            ->prepend($fieldName)
                            ->toArray();

                        $fieldNames = $this->insertUnderKeys(
                            $metaKeys,
                            $fieldNames,
                            $this->decodeCustomFieldTranslation($fieldTranslation)
                        );

                        $this->saveCustomFieldValues($fieldNames, $postId, $originalPostId);
                    }
                }
            }
        }
    }

    public function decodeCustomFieldTranslation($value): string
    {
        // always decode html entities  eg decode &amp; to &.
        return html_entity_decode(str_replace('&#0A;', "\n", $value));
    }

    /**
     * @param $fieldName
     * @param WpmlContentItem $el
     * @return string|null
     */
    public function getCustomFieldId($fieldName, WpmlContentItem $el)
    {
        if (
            strpos($el->getSourceData(), (string)$fieldName) === 0
            && 1 === preg_match('/field-(.*?)-name/U', $el->getKey(), $match)
            && 1 === preg_match('/field-' . $fieldName . '-[0-9].*?-name/', $el->getKey())
        ) {
            return $match[1];
        }
        return null;
    }

    private function removeFieldNameFromStart($field_name, $field_id_string)
    {
        return preg_replace('#' . $field_name . '-?#', '', $field_id_string, 1);
    }

    public static function insertUnderKeys($keys, $array, $value)
    {
        $array[$keys[0]] = count($keys) === 1
            ? $value
            : self::insertUnderKeys(
                array_slice($keys, 1),
                ($array[$keys[0]] ?? []),
                $value
            );

        return $array;
    }

    private function saveCustomFieldValues($fields, $postId, $originalPostId)
    {
        $wpmlApi = new \WPML_WP_API();

        foreach ($fields as $name => $contents) {
            $wpmlApi->delete_post_meta($postId, $name);

            $contents = (array)$contents;
            $single = count($contents) === 1;

            foreach ($contents as $value) {
                // TODO: $value = $this->preserveNumerics($value, $name, $originalPostId, $single);
                $wpmlApi->add_post_meta($postId, $name, $value, $single);
            }
        }
    }
}
<?php

namespace Smartcat\Includes\Services\Categories;

use Smartcat\Includes\Services\Interfaces\CategoriesDatabaseInterface;

class DatabaseService implements CategoriesDatabaseInterface
{
    public function getAllCategories(): array
    {
        return get_categories();
    }

    public function getAllCategoryIds(): array
    {
        $categories = $this->getAllCategories();
        return array_map(function ($category) {
            return $category->term_taxonomy_id;
        }, $categories);
    }

    public function getCategory($id)
    {
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->terms} WHERE term_id = %d";
        $args = [$id];
        return $wpdb->get_row(
            $wpdb->prepare($query, $args)
        );
    }

    public function getCategoryDescription($id)
    {
        global $wpdb;
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT description FROM {$wpdb->term_taxonomy} WHERE term_id = %d",
                [$id]
            )
        )[0];
    }

    public function getCategoryParent($id)
    {
        global $wpdb;
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT parent FROM {$wpdb->term_taxonomy} WHERE term_id = %d",
                [$id]
            )
        )[0];
    }

    public function getCategoryIdsByLang(string $lang): array
    {
        global $wpdb;
        $query = "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type = %s AND language_code = %s";
        $args = ['tax_category', $lang];

        $categoriesIds = $wpdb->get_results(
            $wpdb->prepare($query, $args)
        );

        return array_map(function ($item) {
            return (int)$item->element_id;
        }, $categoriesIds);
    }

    public function getCategoriesByLang(string $lang): array
    {
        $collection = [];

        foreach ($this->getCategoryIdsByLang($lang) as $id) {
            $category = $this->fetchTerm($id);
            if (is_null($category)) {
                continue;
            }
            $collection[] = [
                'id' => (int)$category->term_id,
                'title' => $category->name,
                'description' => $this->getCategoryDescription($id),
                'parent' => (int)$this->getCategoryParent($id),
            ];
        }

        return $collection;
    }

    private function fetchTerm($id)
    {
        global $wpdb;

        $query = "SELECT * FROM {$wpdb->terms} WHERE term_id = %d";
        $args = [$id];

        return $wpdb->get_row(
            $wpdb->prepare($query, $args)
        );
    }
}
<?php

namespace Smartcat\Includes\Services\Categories;

use Smartcat\Includes\Requests\PushCategoriesRequest;
use Smartcat\Includes\Services\Interfaces\CategoriesDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PushService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var CategoriesDatabaseInterface
     */
    private $databaseService;

    public function __construct(
        WpmlInterface               $wpmlService,
        CategoriesDatabaseInterface $databaseService
    )
    {
        $this->wpmlService = $wpmlService;
        $this->databaseService = $databaseService;
    }

    public function push(PushCategoriesRequest $request)
    {
        foreach ($request->getCategories() as $category) {
            $categoryId = $this->findTargetCategory($category['id'], $category['targetLang']);
            if (empty($categoryId)) {
                $this->insert($category, $request->getSourceLang());
            } else {
                $this->update($category, $categoryId);
            }
        }
    }

    private function insert(array $categoryData, string $sourceLang)
    {
        $categoryId = wp_insert_category(
            $this->prepareData($categoryData)
        );
        $this->wpmlService->addTranslation($categoryId, $categoryData['id'], $categoryData['targetLang'], $sourceLang, 'tax_category');
    }

    private function update(array $categoryData, int $categoryId)
    {
        wp_insert_category(
            $this->prepareData($categoryData, $categoryId)
        );
    }

    private function findTargetCategory(int $termId, string $lang)
    {
        $trid = $this->wpmlService->getTrid($termId, 'tax_category');
        $translations = $this->wpmlService->getTranslations($trid, 'tax_category');
        return $translations[$lang]->element_id ?? NULL;
    }

    private function prepareData(array $categoryData, $categoryId = NULL): array
    {
        $categoryData = [
            'cat_name' => $categoryData['title'],
            'category_description' => $categoryData['description'],
            'category_parent' => $categoryData['parent']
        ];
        if (!empty($categoryId)) {
            $categoryData['cat_ID'] = $categoryId;
        }
        return $categoryData;
    }
}
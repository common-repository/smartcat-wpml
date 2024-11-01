<?php

namespace Smartcat\Includes\Services\Categories;

use Smartcat\Includes\Requests\ImportPostsRequest;
use Smartcat\Includes\Services\Interfaces\CategoriesDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class ImportService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var CategoriesDatabaseInterface
     */
    private $categoriesDatabase;

    public function __construct(
        WpmlInterface               $wpmlService,
        CategoriesDatabaseInterface $categoriesDatabase
    )
    {
        $this->wpmlService = $wpmlService;
        $this->categoriesDatabase = $categoriesDatabase;
    }

    // TODO: доделать
    public function import(ImportPostsRequest $request): array
    {
        $categoriesIds = $this->categoriesDatabase->getAllCategoryIds();
        $ids = $this->wpmlService->getIdsBySourceElements(
            $categoriesIds,
            $request->getTargetLanguages(),
            'tax_category'
        );
        return array_map(function ($id) {
            $category = $this->categoriesDatabase->getCategory($id);
            return [
                'id' => $id,
                'title' => $category->name,
                'description' => $this->categoriesDatabase->getCategoryDescription($id),
                'parent' => $this->categoriesDatabase->getCategoryParent($id),
                // 'lang' => $categoryRow->language_code
            ];
        }, $ids);
    }
}
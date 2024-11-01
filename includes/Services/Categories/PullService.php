<?php

namespace Smartcat\Includes\Services\Categories;

use Smartcat\Includes\Requests\PullCategoriesRequest;
use Smartcat\Includes\Services\Interfaces\CategoriesDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PullService
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

    public function getCategories(PullCategoriesRequest $request): array
    {
        return $this->databaseService->getCategoriesByLang($request->getLang());
    }
}
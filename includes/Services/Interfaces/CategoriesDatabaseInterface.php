<?php

namespace Smartcat\Includes\Services\Interfaces;

interface CategoriesDatabaseInterface
{
    public function getAllCategories(): array;

    public function getAllCategoryIds(): array;

    public function getCategory($id);

    public function getCategoryDescription($id);

    public function getCategoryParent($id);

    public function getCategoryIdsByLang(string $lang): array;

    public function getCategoriesByLang(string $lang): array;
}
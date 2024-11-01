<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetBrands extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'brands_list.*.item_name',
            'brands_list.*.item_desc',
        ];
    }
}
<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class PriceList extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'price_list.*.title',
            'price_list.*.item_description',
        ];
    }
}
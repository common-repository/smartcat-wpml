<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetPriceList extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'price_list.*.item_title',
            'price_list.*.item_text',
        ];
    }
}
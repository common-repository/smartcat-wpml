<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetCarousel extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'items_list.*.item_title',
            'items_list.*.item_text',
        ];
    }
}
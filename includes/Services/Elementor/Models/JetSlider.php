<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetSlider extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'item_list.*.item_title',
            'item_list.*.item_subtitle',
            'item_list.*.item_desc',
            'item_list.*.item_button_primary_text',
            'item_list.*.item_button_secondary_text',
        ];
    }
}
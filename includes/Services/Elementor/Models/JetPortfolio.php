<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetPortfolio extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'all_filter_label',
            'view_more_button_text',
            'image_list.*.item_title',
            'image_list.*.item_desc',
            'image_list.*.item_button_text',
        ];
    }
}
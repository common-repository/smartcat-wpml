<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class PriceTable extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'ribbon_title',
            'ribbon_description',
            'footer_additional_info',
            'heading',
            'sub_heading',
            'period',
            'features_list.*.item_text',
            'button_text',
        ];
    }
}
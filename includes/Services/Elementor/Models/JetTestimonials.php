<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetTestimonials extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'item_list.*.item_comment',
            'item_list.*.item_name',
            'item_list.*.item_position',
        ];
    }
}
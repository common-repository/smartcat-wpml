<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class IconList extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'icon_list.*.text'
        ];
    }
}
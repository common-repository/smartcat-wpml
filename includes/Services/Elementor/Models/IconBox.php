<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class IconBox extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title_text',
            'description_text',
        ];
    }
}
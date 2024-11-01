<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Progress extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title',
            'inner_text',
        ];
    }
}
<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class TextPath extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'text'
        ];
    }
}
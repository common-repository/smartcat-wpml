<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Button extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'text'
        ];
    }
}
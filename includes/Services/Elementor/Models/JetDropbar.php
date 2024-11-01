<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetDropbar extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'button_text',
            'simple_content',
        ];
    }
}
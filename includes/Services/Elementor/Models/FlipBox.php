<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class FlipBox extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title_text_a',
            'description_text_a',
            'title_text_b',
            'description_text_b',
            'button_text',
        ];
    }
}
<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetAnimatedBox extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'front_side_title',
            'front_side_subtitle',
            'front_side_description',
            'back_side_title',
            'back_side_description',
            'back_side_button_text',
        ];
    }
}
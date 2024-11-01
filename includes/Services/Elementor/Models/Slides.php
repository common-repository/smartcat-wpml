<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Slides extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'slides.*.heading',
            'slides.*.description',
            'slides.*.button_text',
        ];
    }
}
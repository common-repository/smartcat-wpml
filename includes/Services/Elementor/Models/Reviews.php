<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Reviews extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'slides.*.content',
            'slides.*.name',
            'slides.*.title',
        ];
    }
}
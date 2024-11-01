<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class TestimonialCarousel extends BaseModel
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
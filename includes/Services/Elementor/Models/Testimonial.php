<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Testimonial extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'testimonial_content',
            'testimonial_name',
            'testimonial_job',
        ];
    }
}
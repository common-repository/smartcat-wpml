<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class CallToAction extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title',
            'description',
            'button'
        ];
    }
}
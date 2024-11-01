<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetServices extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'services_title',
            'services_description',
            'button_text',
        ];
    }
}
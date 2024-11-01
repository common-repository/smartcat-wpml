<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Alert extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'alert_title',
            'alert_description',
        ];
    }
}
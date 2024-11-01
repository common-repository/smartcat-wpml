<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetButton extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'button_label_normal',
            'button_label_hover',
        ];
    }
}
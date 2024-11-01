<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Toggle extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'tabs.*.tab_title',
            'tabs.*.tab_content',
        ];
    }
}
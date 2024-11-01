<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Accordion extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'tabs.*.tab_title',
            'tabs.*.tab_content',
        ];
    }
}
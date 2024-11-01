<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetAccordion extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'toggles.*.item_label'
        ];
    }
}
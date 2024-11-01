<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetTabs extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'tabs.*.item_label',
            'tabs.*.item_editor_content',
        ];
    }
}
<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetImageAccordion extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'item_list.*.item_title',
            'item_list.*.item_desc',
            'item_list.*.item_link_text',
        ];
    }
}
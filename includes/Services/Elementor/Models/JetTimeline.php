<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetTimeline extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'cards_list.*.item_title',
            'cards_list.*.item_desc',
        ];
    }
}
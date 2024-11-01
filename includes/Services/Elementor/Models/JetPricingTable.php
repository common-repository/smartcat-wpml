<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetPricingTable extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'button_text',
            'button_fold_text',
            'button_unfold_text',
            'title',
            'subtitle',
            'price_suffix',
            'features_list.*.item_text',
        ];
    }
}
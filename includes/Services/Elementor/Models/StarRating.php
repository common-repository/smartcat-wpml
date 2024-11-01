<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class StarRating extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title',
        ];
    }
}
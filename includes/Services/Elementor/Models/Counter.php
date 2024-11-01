<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Counter extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title',
        ];
    }
}
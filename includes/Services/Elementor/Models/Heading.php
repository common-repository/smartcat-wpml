<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Heading extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title'
        ];
    }
}
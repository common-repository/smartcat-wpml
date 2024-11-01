<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetHeadline extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'first_part',
            'second_part',
        ];
    }
}
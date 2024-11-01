<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class TextEditor extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'editor'
        ];
    }
}
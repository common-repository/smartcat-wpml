<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class TableOfContents extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'title'
        ];
    }
}
<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetBanner extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'banner_title',
            'banner_text',
        ];
    }
}
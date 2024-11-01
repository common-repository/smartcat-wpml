<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class AnimatedHeadline extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'before_text',
            'highlighted_text',
            'rotating_text',
            'after_text',
        ];
    }
}
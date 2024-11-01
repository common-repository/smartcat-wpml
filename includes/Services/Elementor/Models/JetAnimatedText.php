<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetAnimatedText extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'before_text_content',
            'after_text_content',
            'animated_text_list.*.item_text',
        ];
    }
}
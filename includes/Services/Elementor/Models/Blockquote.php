<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Blockquote extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'blockquote_content',
            'author_name',
            'tweet_button_label',
        ];
    }
}
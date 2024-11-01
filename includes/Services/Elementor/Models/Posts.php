<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Posts extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'classic_read_more_text',
            'cards_read_more_text',
            'pagination_prev_label',
            'pagination_next_label',
            'text',
            'load_more_no_posts_custom_message'
        ];
    }
}
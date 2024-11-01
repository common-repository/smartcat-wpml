<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetTeamMember extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'member_first_name',
            'member_last_name',
            'member_position',
            'member_description',
            'button_text',
        ];
    }
}
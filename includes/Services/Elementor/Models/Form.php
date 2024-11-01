<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class Form extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'success_message',
            'error_message',
            'required_field_message',
            'invalid_message',
            'button_text',
            'step_previous_label',
            'step_next_label',
            'form_name',
            'form_fields.*.field_label',
            'form_fields.*.placeholder'
        ];
    }
}
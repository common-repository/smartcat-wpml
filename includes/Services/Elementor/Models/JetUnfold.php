<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetUnfold extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'editor',
            'button_fold_text',
            'button_unfold_text',
        ];
    }
}
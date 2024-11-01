<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetDownloadButton extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'download_label'
        ];
    }
}
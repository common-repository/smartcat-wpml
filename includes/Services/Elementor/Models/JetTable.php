<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class JetTable extends BaseModel
{
    public function getTranslatableFields(): array
    {
        return [
            'table_header.*.cell_text',
            'table_body.*.cell_text',
            'table_footer.*.cell_text',
        ];
    }
}
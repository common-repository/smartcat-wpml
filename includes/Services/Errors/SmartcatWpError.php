<?php

namespace Smartcat\Includes\Services\Errors;

class SmartcatWpError extends \WP_Error
{
    public function showError()
    {
        echo "<p class='notice notice-error' style='margin: 10px 0;'>{$this->get_error_message()}</p>";
    }
}
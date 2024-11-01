<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class WpmlPushStringsRequest
{
    private $strings = [];

    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->strings = $request->get_params();
    }

    /**
     * @return array
     */
    public function getStrings(): array
    {
        return $this->strings;
    }
}
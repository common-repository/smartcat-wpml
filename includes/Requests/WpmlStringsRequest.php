<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class WpmlStringsRequest
{
    /**
     * @var string
     */
    private $lang = NULL;

    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->lang = $request->get_param('language');
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }
}
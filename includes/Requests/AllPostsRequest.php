<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class AllPostsRequest
{
    /**
     * @var string
     */
    private $lang;

    public function __construct(ArrayAccess $request)
    {
        $this->lang = $request->get_param('lang');
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }
}
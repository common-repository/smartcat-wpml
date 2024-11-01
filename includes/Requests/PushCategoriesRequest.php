<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class PushCategoriesRequest
{
    /**
     * @var array
     */
    private $categories;

    /**
     * @var string
     */
    private $sourceLang;


    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->categories = $request->get_param('categories');
        $this->sourceLang = $request->get_param('sourceLang');
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return string
     */
    public function getSourceLang()
    {
        return $this->sourceLang;
    }
}
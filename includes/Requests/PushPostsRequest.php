<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class PushPostsRequest
{
    /**
     * @var string
     */
    private $lang = NULL;

    /**
     * @var array
     */
    private $documents;


    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->lang = $request->get_param('sourceLang'); // FIXME: хочу быть language
        $this->documents = $request->get_param('documents');
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return array
     */
    public function getPosts()
    {
        return $this->documents;
    }
}
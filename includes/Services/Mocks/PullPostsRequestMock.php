<?php

namespace Smartcat\Includes\Services\Mocks;

class PullPostsRequestMock implements \ArrayAccess
{
    private $data = [
        'language' => 'ru',
        'ids' => '[]',
        'meta' => '[]',
        'offset' => 0,
        'limit' => 10,
        'date' => ''
    ];

    public function get_param($key = '')
    {
        return $this->data[$key];
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}
<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class PullPostsRequest
{
    /**
     * @var string
     */
    private $lang = NULL;

    /**
     * @var array
     */
    private $ids = [];

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    private $date = NULL;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->lang = $request->get_param('sourceLang'); // FIXME: хочу быть language
        $this->ids = $request->get_param('ids');
        $this->meta = $request->get_param('metaDataKeys'); // FIXME: хочу быть meta
        $this->date = $request->get_param('date');
        $this->limit = $request->get_param('limit');
        $this->offset = $request->get_param('offset');
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
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
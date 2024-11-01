<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class ImportPostsRequest
{
    /**
     * @var string
     */
    private $sourceLang;

    /**
     * @var array
     */
    private $targetLanguages;

    /**
     * @var array
     */
    private $metaDataKeys = [];

    /**
     * @var array
     */
    private $ids;

    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->sourceLang = $request->get_param('sourceLang');
        $this->targetLanguages = $request->get_param('targetLanguages');
        $this->ids = $request->get_param('ids');
        $this->meta = $request->get_param('metaDataKeys');
    }

    /**
     * @return string
     */
    public function getSourceLang()
    {
        return $this->sourceLang;
    }

    /**
     * @return array
     */
    public function getTargetLanguages()
    {
        return $this->targetLanguages;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    public function isIdsEmpty(): bool
    {
        return empty($this->ids);
    }

    public function getMetaDataKeys()
    {
        return $this->metaDataKeys;
    }
}
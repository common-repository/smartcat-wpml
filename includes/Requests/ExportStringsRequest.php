<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;

class ExportStringsRequest
{
    /**
     * @var string
     */
    private $locale = NULL;

    /**
     * @var string
     */
    private $domains = NULL;

    /**
     * @param ArrayAccess|\WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->locale = $request->get_param('locale');
        $this->domains = $request->get_param('domains');
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        return empty($this->domains)
            ? []
            : explode(',', $this->domains);
    }
}
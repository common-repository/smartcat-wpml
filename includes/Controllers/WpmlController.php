<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Requests\WpmlStringsRequest;
use Smartcat\Includes\Services\Wpml as WpmlService;
use WP_REST_Request;

class WpmlController
{
    /**
     * @var WpmlService
     */
    private $wpmlService;

    public function __construct()
    {
        $this->wpmlService = new WpmlService();
    }

    public function locales(WP_REST_Request $request): array
    {
        return $this->wpmlService->getActiveLocales();
    }

    public function strings(WP_REST_Request $request)
    {
        $request = new WpmlStringsRequest($request);
        $domains = explode(',', get_option('smartcat_string_domains'));
        return $this->wpmlService->getStrings($request->getLang(), $domains);
    }

    public function pushStrings(WP_REST_Request $request)
    {
        foreach ($request->get_param('data') as $string) {
            $this->wpmlService->registerString(
                $string['context'],
                $string['name'],
                $request->get_param('language'),
                $string['content']
            );
        }
    }
}
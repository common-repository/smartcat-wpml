<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Requests\ExportStringsRequest;
use Smartcat\Includes\Services\Strings\StringsService;
use WP_REST_Request;

class StringController
{
    public function domains()
    {
        try {
            if (!function_exists('icl_st_get_contexts')) {
                wp_send_json_success([]);
                return;
            }

            wp_send_json_success(
                (new StringsService())->getDomains()
            );
        } catch (\Throwable $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function export(WP_REST_Request $request)
    {
        try {
            $request = new ExportStringsRequest($request);
            wp_send_json_success(
                (new StringsService())->export(
                    $request->getLocale(),
                    $request->getDomains()
                )
            );
        } catch (\Throwable $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function import(WP_REST_Request $request)
    {

    }
}
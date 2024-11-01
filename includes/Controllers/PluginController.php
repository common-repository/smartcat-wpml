<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Services\Plugin\PluginStatusService;
use Smartcat\Includes\Services\Wpml as WpmlService;
use WP_REST_Request;
use Smartcat\Includes\Services\Plugin\Migrations;

class PluginController
{
    private $statusService;

    public function __construct()
    {
        $this->statusService = new PluginStatusService(
            new WpmlService()
        );
    }

    public function status(): array
    {
        return $this->statusService->statusInformation();
    }

    public function shell(WP_REST_Request $request)
    {
        //
    }

    public function logs(WP_REST_Request $request)
    {
        if ($request->get_param('secret') === SMARTCAT_LOGS_SECRET) {
            return sc_log()->getLogs(
                $request->get_param('type'),
                $request->get_param('query'),
                $request->get_param('fromDate'),
                $request->get_param('toDate'),
                $request->get_param('offset') ?? 0,
                $request->get_param('limit') ?? 100,
                $request->get_param('orderBy'),
                $request->get_param('order') ?? 'DESC'
            );
        }
    }

    public function runMigrations(WP_REST_Request $request)
    {
        if ($request->get_param('secret') === SMARTCAT_LOGS_SECRET) {
            $migrations = new Migrations();
            $migrations->run();

            return [
                'status' => 'success',
                'message' => 'Migrations run successfully'
            ];
        }
    }
}

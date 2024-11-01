<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Requests\CredentialsRequest;
use WP_REST_Request;

class CallbackController
{
    public function credentials(WP_REST_Request $request)
    {
        $request = new CredentialsRequest($request);

        update_option('smartcat_account_id', $request->getAccountId());
        update_option('smartcat_api_key', $request->getSecretKey());
        update_option('smartcat_hub_key', $request->getHubKey());
        update_option('smartcat_api_host', $request->getSmartcatHost());
        update_option('smartcat_hub_host', $request->getHubHost());
    }
}
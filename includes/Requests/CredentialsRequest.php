<?php

namespace Smartcat\Includes\Requests;

use ArrayAccess;
use WP_REST_Request;

class CredentialsRequest
{
    /**
     * @var string
     */
    private $accountId;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $hubKey;

    /**
     * @var string
     */
    private $smartcatHost;

    /**
     * @var string
     */
    private $hubHost;

    /**
     * @param ArrayAccess|WP_REST_Request $request
     */
    public function __construct(ArrayAccess $request)
    {
        $this->accountId = $request->get_param('accountId');
        $this->secretKey = $request->get_param('secretKey');
        $this->hubKey = $request->get_param('hubKey');
        $this->smartcatHost = $request->get_param('smartcatHost');
        $this->hubHost = $request->get_param('hubHost');
    }

    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return string
     */
    public function getHubKey()
    {
        return $this->hubKey;
    }

    /**
     * @return string
     */
    public function getSmartcatHost()
    {
        return $this->smartcatHost;
    }

    /**
     * @return string
     */
    public function getHubHost()
    {
        return $this->hubHost;
    }
}
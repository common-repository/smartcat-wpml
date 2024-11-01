<?php

namespace Smartcat\Includes\Services\Sentry;

use WP_Http;

class SentryService
{
    /** @var string */
    private $level = 'error';

    /** @var \Throwable */
    private $exception = null;

    /** @var array */
    private $extra = [];

    private $message = '';

    /**
     * fatal|error|warning|info|debug
     * @param string $level
     * @return void
     */
    public function level(string $level): SentryService
    {
        $this->level = $level;

        return $this;
    }

    public function extra(array $data): SentryService
    {
        $this->extra = $data;

        return $this;
    }

    public function message(string $message): SentryService
    {
        $this->message = $message;

        return $this;
    }

    public function exception($e): SentryService
    {
        $this->exception = $e;

        return $this;
    }

    private function getRequestData(): array
    {
        return [
            'event_id' => $this->getEventId(),
            'timestamp' => microtime(true),
            'platform' => 'php',
            'level' => $this->level,
            'logger' => 'sc.wp.logger',
            'server_name' => $_SERVER['SERVER_NAME'],
            'release' => 'smartcat-wpml-plugin@' . SMARTCAT_WPML_VERSION,
            'environment' => SMARTCAT_DEV_MODE || SC_LOCAL_ENV ? 'local' : 'production',
            'contexts' => [
                'runtime' => [
                    'name' => 'php',
                    'version' => PHP_VERSION
                ]
            ],
            'extra' => array_merge([
                'url' => $this->getCurrentUrl(),
                'smartcat_account_id' => get_option('smartcat_account_id'),
                'smartcat_api_host' => get_option('smartcat_api_host'),
                'smartcat_hub_host' => get_option('smartcat_hub_host'),
            ], $this->extra),
            'exception' => [
                'values' => [
                    $this->getExceptionData()
                ]
            ]
        ];
    }

    private function getExceptionData(): array
    {
        return !is_null($this->exception) ? [
            'type' => $this->exception->getMessage(),
            'value' => $this->exception->getMessage(),
            'stacktrace' => [
                'frames' => $this->getFrames()
            ]
        ] : [
            'type' => $this->message,
            'value' => $this->message
        ];
    }

    private function getFrames(): array
    {
        $frames = [];

        $lines = explode("\n", $this->exception->getTraceAsString());

        foreach ($this->exception->getTrace() as $index => $item) {
            $frames[] = [
                'filename' => basename($item['file']),
                'lineno' => $item['line'],
                'in_app' => true,
                'abs_path' => $item['file'],
                'context_line' => $lines[$index] ?? ''
            ];
        }

        return $frames;
    }

    /**
     * Copied from Sentry PHP SDK
     * vendor/sentry/sentry/src/Util/SentryUid.php
     *
     * @return string
     * @throws \Exception
     */
    private function getEventId(): string
    {
        $uuid = bin2hex(random_bytes(16));

        return sprintf('%08s%04s4%03s%04x%012s',
            // 32 bits for "time_low"
            substr($uuid, 0, 8),
            // 16 bits for "time_mid"
            substr($uuid, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            substr($uuid, 13, 3),
            // 16 bits:
            // * 8 bits for "clk_seq_hi_res",
            // * 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            hexdec(substr($uuid, 16, 4)) & 0x3FFF | 0x8000,
            // 48 bits for "node"
            substr($uuid, 20, 12)
        );
    }

    public function send()
    {
        $http = new WP_Http();
        $host = 'https://sentry.io/api/' . SC_SENTRY_PROJECT_ID . '/store/';

        $http->request($host, [
            'method' => 'POST',
            'timeout' => 30,
            'body' => json_encode($this->getRequestData()),
            'headers' => [
                'X-Sentry-Auth' => 'Sentry sentry_version=7, sentry_key=' . SC_SENTRY_KEY . ', sentry_client=raven-bash/0.1',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    private function getCurrentUrl(): string
    {
        return sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['REQUEST_URI']
        );
    }
}
<?php

namespace Smartcat\Includes\Services\API;

use Smartcat\Includes\Services\API\Contracts\HubClientInterface;
use Smartcat\Includes\Services\API\Models\Project;
use Smartcat\Includes\Services\API\Models\TranslatedItem;
use Smartcat\Includes\Services\Errors\SmartcatWpError;
use WP_Http;

class HubClient implements HubClientInterface
{
    public function createProject(string $name, string $sourceLanguage, array $sourceLanguages, array $workflowStages)
    {
        $data = [
            'scProjectName' => $name,
            'scAccountId' => get_option('smartcat_account_id'),
            'sourceLanguage' => sc_locale()->map($sourceLanguage),
            'targetLanguages' => array_map(function ($locale) {
                return sc_locale()->map($locale);
            }, $sourceLanguages),
            'stageTypes' => $workflowStages
        ];

        $response = $this->sendRequest('api/wpml-app/project', $data);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    public function importTranslations($postName, $postId, $projectId, $sourceLang, $targetLang, $content)
    {
        $data = [
            'wpmlPostName' => $postName,
            'wpmlPostId' => $postId,
            'scAccountId' => get_option('smartcat_account_id'),
            'scProjectId' => $projectId,
            'sourceLanguage' => sc_locale()->map($sourceLang),
            'targetLanguage' => sc_locale()->map($targetLang),
            'properties' => [
                'wpml-meta' => $content['properties']
            ],
            'items' => $content['items']
        ];

        $response = $this->sendRequest('api/wpml-app/import', $data);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    public function getProject($projectId, $sourceLanguage)
    {
        $data = [
            'scProjectId' => $projectId,
            'scAccountId' => get_option('smartcat_account_id'),
            'sourceLanguage' => sc_locale()->map($sourceLanguage)
        ];

        $response = $this->sendRequest('api/wpml-app/project', $data);

        if (is_wp_error($response)) {
            return $response;
        }

        $data = $this->getJsonFromBody($response);

        return (new Project())
            ->setId($data['id'] ?? null)
            ->setDocuments($data['documents'] ?? [])
            ->setDeadline($data['deadline'] ?? null)
            ->setName($data['name'] ?? null);
    }

    public function exportBegin($postId, $projectId, $targetLanguage)
    {
        $data = [
            'wpmlPostId' => $postId,
            'targetLanguage' => sc_locale()->map($targetLanguage),
            'scProjectId' => $projectId,
            'scAccountId' => get_option('smartcat_account_id')
        ];

        $response = $this->sendRequest('api/wpml-app/export-begin', $data);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    /**
     * @param array $exportInfo
     * @return array|TranslatedItem[]|SmartcatWpError
     */
    public function exportResult(array $exportInfo)
    {
        $response = $this->sendRequest('api/wpml-app/export-result', $exportInfo);

        if (is_wp_error($response)) {
            return $response;
        }

        $data = $this->getJsonFromBody($response);

        return [
            'smartcatDocumentId' => $data['exportInfo']['smartcatDocumentId'] ?? null,
            'items' => !is_null($data['items']) ? $data['items'] : [],
            'meta' => isset($data['properties']['wpml-meta'])
                ? json_decode($data['properties']['wpml-meta'], true)
                : []
        ];
    }

    public function addCommentToDocument($documentId, $comment)
    {
        $data = ['commentText' => $comment];

        $response = $this->sendRequest("api/wpml-app/add-document-comment/$documentId", $data);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    public function workspaceInfo($accountID)
    {
        $response = $this->sendRequest("api/wpml-app/sc/workspace?accountId=$accountID", [], 'GET');

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    public function registerApiCredentials($host, $accountID, $apiKey)
    {
        $data = [
            'smartcatAccountId' => $accountID,
            'apiKey' => $apiKey,
        ];

        $response = $this->sendRequest('/api/smartcat/registration', $data, 'POST', $host);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    public function sendMixpanelEvent($event, $status = null, $eventParams = [])
    {
        if (! SmartcatClient::isAuthorized()) {
            return;
        }

        $eventParams['siteUrl'] = get_site_url();

        $data = [
            'eventId' => $event,
            'accountId' => get_option('smartcat_account_id'),
            'eventParams' => $eventParams
        ];

        if (is_bool($status)) {
            $data['status'] = $status ? 'Success' : 'Failed';
        }

        $response = $this->sendRequest('api/wpml-app/mixpanel', $data);

        if (is_wp_error($response)) {
            return $response;
        }

        return $this->getJsonFromBody($response);
    }

    private function sendRequest($uri, $body = [], $method = 'POST', $host = NULL)
    {
        $http = new WP_Http();
        $host = $host ?? $this->getHost();
        $endpoint = $host . $uri;

        sc_log()->info("Request to API: $uri", [
            'endpoint' => $endpoint,
            'method' => $method,
            'data' => $body
        ]);

        $response = $http->request($endpoint, [
            'method' => $method,
            'timeout' => 30,
            'body' => $method === 'GET' ? $body : json_encode($body),
            'headers' => [
                'Authorization' => $this->getAuthorizationToken(),
                'Content-Type' => 'application/json'
            ]
        ]);

        if (!$this->checkResponse($response)) {
            sc_log()->error("API request error: $uri", [
                'data' => $body,
                'endpoint' => $endpoint,
                'responseBody' => is_array($response) ? $response['body'] : $response,
                'responseErrors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);

            return new SmartcatWpError(500, $this->errorMessages($uri));
        }

        return $response;
    }

    private function getAuthorizationToken(): string
    {
        return 'SmartcatApi ' . base64_encode(get_option('smartcat_account_id') . ':' . get_option('smartcat_api_key'));
    }

    private function getHost(): string
    {
        $length = strlen('/');

        $host = get_option('smartcat_hub_host');

        if (empty($host)) {
            $host = SMARTCAT_IHUB_HOST;
        }

        if (substr($host, -$length) === '/') {
            return $host;
        }

        return $host . '/';
    }

    private function checkResponse($response): bool
    {
        if ($response instanceof \WP_Error) {
            return false;
        }

        if (isset($response['response'])) {
            preg_match('/20[01]/', $response['response']['code'], $output);
            return !empty($output);
        }

        return false;
    }

    private function getJsonFromBody($response)
    {
        return json_decode($response['body'], true);
    }

    private function errorMessages($uri): string
    {
        $messages = [
            'api/wpml-app/project' => 'Error when creating or getting a project in Smartcat',
            'api/wpml-app/import' => 'Error when importing content into Smartcat',
            'api/smartcat/registration' => 'Error while registering API keys',
            'api/wpml-app/export-result' => 'Error when getting ready translations from Smartcat',
            'api/wpml-app/export-begin' => 'Error getting data to export translations from Smartcat'
        ];

        return $messages[$uri] ?? 'Unknown error';
    }
}
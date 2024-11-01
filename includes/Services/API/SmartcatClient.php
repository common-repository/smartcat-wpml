<?php

namespace Smartcat\Includes\Services\API;

use Smartcat\Includes\Services\API\Contracts\SmartcatClientInterface;
use Smartcat\Includes\Services\API\Models\Project;
use Smartcat\Includes\Services\Errors\SmartcatWpError;
use WP_Http;

class SmartcatClient implements SmartcatClientInterface
{
    public function logInRedirect()
    {
        $prefix = !SC_LOCAL_ENV ? '/app' : '';
        $url = self::getAuthHost() . "$prefix/sign-in?source=wpml_app&backUrl=/integrations/wpml-sign-in?wordpressUrl=" . get_site_url();
        header("Location: $url");
    }

    public static function isAuthorized(): bool
    {
        return !empty(get_option('smartcat_account_id'))
            && !empty(get_option('smartcat_api_key'))
            && !empty(get_option('smartcat_hub_key'))
            && !empty(get_option('smartcat_api_host'))
            && !empty(get_option('smartcat_hub_host'));
    }

    public function createProject($postId, $sourceLanguage, $targetLanguages)
    {
        $data = [
            'name' => "WPML Integration: post - $postId",
            'sourceLanguage' => sc_locale()->map($sourceLanguage),
            'targetLanguages' => array_map(function ($locale) {
                return sc_locale()->map($locale);
            }, $targetLanguages),
            'enableProjectTasks' => true,
            'externalTag' => 'ihub-wpml'
        ];

        $response = $this->sendRequest('api/integration/v1/project/create', ['value' => json_encode($data)]);

        $responseData = json_decode($response['body'], true);

        return $responseData['id'] ?? NULL;
    }

    public function updateProjectDeadline($projectId, $projectName, $deadline)
    {
        $data = [
            'name' => $projectName,
            'deadline' => $deadline
        ];

        $response = $this->sendRequest("api/integration/v1/project/$projectId", json_encode($data), 'PUT', [
            'Content-type' => 'application/json'
        ]);

        if (!$this->checkResponse($response)) {
            smartcat_logger()->error('Error update project deadline', [
                'smartcat_project_id' => $projectId,
                'api_host' => $this->getHost(),
                'api_endpoint' => "api/integration/v1/project/$projectId",
                'request_data' => $data,
                'response_body' => is_array($response) ? $response['body'] : $response,
                'response_errors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);
            return new SmartcatWpError(500, 'Error update project deadline');
        }
    }

    public function getProject($projectId)
    {
        $response = $this->sendRequest("api/integration/v1/project/$projectId", [], 'GET');

        if (!$this->checkResponse($response)) {
            sc_log()->error('Error loading project from Smartcat account', [
                'smartcat_project_id' => $projectId,
                'api_host' => $this->getHost(),
                'response' => $response['response'],
                'api_endpoint' => "api/integration/v1/project/$projectId",
                'response_body' => is_array($response) ? $response['body'] : $response,
                'response_errors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);

            if ($response['response']['code'] === 404 || $response['response']['code'] === 403) {
                smartcat_logger()->warn("Project '$projectId' not found or account have not access. Translation requests with this project will be deleted.");
                smartcat_dm()->deleteDocumentsByProjectId($projectId);
            }

            return new SmartcatWpError(500, 'Error loading project from Smartcat account');
        }

        return $this->getJsonFromBody($response);
    }

    /**
     * @throws \Exception
     */
    public function getAccountProjects()
    {
        $response = $this->sendRequest('api/integration/v1/project/list', [], 'GET');

        if (!$this->checkResponse($response)) {

            smartcat_logger()->error('Error loading project from Smartcat account', [
                'api_host' => $this->getHost(),
                'api_endpoint' => "api/integration/v1/project/list",
                'response_body' => is_array($response) ? $response['body'] : $response,
                'response_errors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);

            return new SmartcatWpError(500, 'Error loading project list from Smartcat account');
        }
        return $this->getJsonFromBody($response);
    }

    public function availableProjectMT($smartcatProjectId)
    {
        $response = $this->sendRequest("api/integration/v1/project/$smartcatProjectId/mt/available", [], 'GET');
        if (!$this->checkResponse($response)) {

            smartcat_logger()->error('Error loading project from Smartcat account', [
                'smartcat_project_id' => $smartcatProjectId,
                'api_host' => $this->getHost(),
                'response' => $response['response'],
                'api_endpoint' => "api/integration/v1/project/$smartcatProjectId/mt/available",
                'response_body' => is_array($response) ? $response['body'] : $response,
                'response_errors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);

            return new SmartcatWpError(500, 'Error getting Smartcat MT');
        }
        return $this->getJsonFromBody($response);
    }

    public function setupMT($smartcatProjectId, $mtList)
    {
        $response = $this->sendRequest("api/integration/v1/project/$smartcatProjectId/mt", json_encode($mtList), 'POST', [
            'Content-type' => 'application/json'
        ]);

        if (!$this->checkResponse($response)) {
            smartcat_logger()->error('Error set MT to project', [
                'smartcat_project_id' => $smartcatProjectId,
                'request_data' => $mtList,
                'api_host' => $this->getHost(),
                'response' => $response['response'],
                'requestMethod' => 'POST',
                'api_endpoint' => "api/integration/v1/project/$smartcatProjectId/mt",
                'response_body' => is_array($response) ? $response['body'] : $response,
                'response_errors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);
            return new SmartcatWpError(500, 'Error set MT to projects');
        }
        return $this->getJsonFromBody($response);
    }

    public function addPreTranslationRules($smartcatProjectId, $rules)
    {
        $response = $this->sendRequest("api/integration/v1/project/$smartcatProjectId/pretranslation-rules", json_encode($rules), 'POST', [
            'Content-type' => 'application/json'
        ]);

        if (!$this->checkResponse($response)) {
            smartcat_logger()->error('Error adding pretranslation rules', [
                'smartcat_project_id' => $smartcatProjectId,
                'request_data' => $rules,
                'api_host' => $this->getHost(),
                'response' => $response['response'],
                'requestMethod' => 'POST',
                'api_endpoint' => "api/integration/v1/project/$smartcatProjectId/pretranslation-rules",
                'response_body' => is_array($response) ? $response['body'] : $response,
                'response_errors' => $response instanceof \WP_Error ? $response->get_error_messages() : NULL
            ]);
            return new SmartcatWpError(500, 'Error adding pretranslation rules');
        }
        return $this->getJsonFromBody($response);
    }

    /**
     * @param $limit
     * @param $offset
     * @param $projectName
     * @return array|Project[]
     */
    public function getProjectsList($limit = 100, $offset = 0, $projectName = '')
    {
        $query = [
            'limit' => $limit,
            'offset' => $offset,
            'includeDocuments' => 'false',
            'externalTag' => SC_PROJECT_EXTERNAL_TAG,
            //'includeQuotes' => 'false',
            'includeCustomFields' => 'false',
            'includeClients' => 'false',
            'projectName' => $projectName
        ];

        $response = $this->sendRequest("api/integration/v1/project/list?" . http_build_query($query), [], 'GET', [
            'Content-type' => 'application/json'
        ]);

        $data = $this->getJsonFromBody($response);

        return array_map(function ($project) {
            return (new Project())
                ->setId($project['id'] ?? null)
                ->setDocuments($project['documents'] ?? [])
                ->setDeadline($project['deadline'] ?? null)
                ->setName($project['name'] ?? null)
                ->setExternalTag($project['externalTag'] ?? null)
                ->setSourceLocale($project['sourceLanguage']);
        }, $data);
    }

    public function redirectToProject($projectId)
    {
        header('Location: ' . "{$this->getHost()}projects/$projectId/integrations");
    }

    public static function getAuthHost($withoutSlash = false)
    {
        if (empty(get_option('smartcat_api_host'))) {
            return self::debugMode() ? SMARTCAT_HOST_DEV : SMARTCAT_HOST;
        }

        return !$withoutSlash
            ? get_option('smartcat_api_host')
            : self::removeSlash(get_option('smartcat_api_host'));
    }

    private static function removeSlash($str)
    {
        if (substr($str, -1) === '/') {
            return substr($str, 0, -1);
        }

        return $str;
    }

    public static function debugMode()
    {
        if (empty(get_option('smartcat_debug_mode'))) {
            return SMARTCAT_DEV_MODE;
        }
        return (bool)get_option('smartcat_debug_mode');
    }

    private function sendRequest($uri, $body = [], $method = 'POST', $headers = [])
    {
        $http = new WP_Http();
        $host = $this->getHost() . $uri;

        return $http->request($host, [
            'method' => $method,
            'timeout' => 30,
            'body' => $body,
            'headers' => array_merge([
                'Authorization' => $this->getAuthorizationToken(),
            ], $headers)
        ]);
    }

    private function getAuthorizationToken(): string
    {
        return 'Basic ' . base64_encode(get_option('smartcat_account_id') . ':' . get_option('smartcat_api_key'));
    }

    private function getHost(): string
    {
        $length = strlen('/');

        $host = get_option('smartcat_api_host');

        if (empty($host)) {
            $host = SMARTCAT_HOST;
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
            preg_match('/20[014]/', $response['response']['code'], $output);
            return !empty($output);
        }

        return false;
    }

    private function getJsonFromBody($response)
    {
        return json_decode($response['body'], true);
    }
}
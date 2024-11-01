<?php

namespace Smartcat\Includes\Services\API\Contracts;

interface SmartcatClientInterface
{
    public function logInRedirect();

    public static function isAuthorized(): bool;

    public function createProject($postId, $sourceLanguage, $targetLanguages);

    public function getProject($projectId);

    public function updateProjectDeadline($projectId, $projectName, $deadline);

    public function redirectToProject($projectId);

    public function getAccountProjects();

    public static function getAuthHost();

    public static function debugMode();

    public function availableProjectMT($smartcatProjectId);

    public function setupMT($smartcatProjectId, $mtList);

    public function addPreTranslationRules($smartcatProjectId, $rules);
}
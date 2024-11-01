<?php

namespace Smartcat\Includes\Services\Mocks;

use Ramsey\Uuid\Uuid;
use Smartcat\Includes\Services\API\Contracts\SmartcatClientInterface;

class SmartcatClientMock implements SmartcatClientInterface
{

    public function logInRedirect()
    {
        // TODO: Implement logInRedirect() method.
    }

    public static function isAuthorized(): bool
    {
        return true;
    }

    public function createProject($postId, $sourceLanguage, $targetLanguages)
    {
        // TODO: Implement createProject() method.
    }

    public function getProject($projectId)
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'targetLanguages' => [
                'en', 'ru'
            ],
            'workflowStages' => [
                [
                    'id' => Uuid::uuid4()->toString(),
                    'progress' => 0.0,
                    'stageType' => 'translation'
                ]
            ]
        ];
    }

    public function updateProjectDeadline($projectId, $projectName, $deadline)
    {
        // TODO: Implement updateProjectDeadline() method.
    }

    public function redirectToProject($projectId)
    {
        // TODO: Implement redirectToProject() method.
    }

    public function getAccountProjects()
    {
        // TODO: Implement getAccountProjects() method.
    }

    public static function getAuthHost()
    {
        // TODO: Implement getAuthHost() method.
    }

    public static function debugMode()
    {
        // TODO: Implement debugMode() method.
    }

    public function availableProjectMT($smartcatProjectId)
    {
        return [
            0 => [
                "Id" => "engine:Amazon",
                "Name" => "Amazon Translate",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en"
                ]
            ],
            1 => [
                "Id" => "engine:DeepL",
                "Name" => "DeepL",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en"
                ]
            ],
            2 => [
                "Id" => "engine:Google",
                "Name" => "Google",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en"
                ]
            ],
            3 => [
                "Id" => "engine:Google NMT",
                "Name" => "Google Neural Machine Translation",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en",
                ],
            ],
            4 => [
                "Id" => "engine:Baidu",
                "Name" => "Baidu Translate API",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en",
                ],
            ],
            5 => [
                "Id" => "engine:Microsoft",
                "Name" => "Microsoft Translator",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en",
                ],
            ],
            6 => [
                "Id" => "engine:ModernMT",
                "Name" => "ModernMT",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en",
                ],
            ],
            7 => [
                "Id" => "engine:YandexPaid",
                "Name" => "Yandex",
                "Languages" => [
                    0 => "zh-Hans",
                    1 => "en",
                ],
            ],
        ];
    }

    public function setupMT($smartcatProjectId, $mtList)
    {
        return true;
    }

    public function addPreTranslationRules($smartcatProjectId, $rules)
    {
        return true;
    }
}
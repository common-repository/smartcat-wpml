<?php

namespace Smartcat\Includes\Services\App;

use Smartcat\Includes\Services\API\Contracts\HubClientInterface;
use Smartcat\Includes\Services\API\Contracts\SmartcatClientInterface;
use Smartcat\Includes\Services\Errors\SmartcatWpError;
use Smartcat\Includes\Services\Tools\LocaleMapper;

class SmartcatProjectFactory
{
    /** @var array */
    private $postIds;

    /** @var string */
    private $sourceLocale;

    /** @var array */
    private $targetLocales;

    /** @var string|null */
    private $workflowStage;

    /** @var string|null */
    private $deadline;

    /** @var string */
    private $id;

    /** @var HubClientInterface */
    private $hubClient;

    /** @var SmartcatClientInterface */
    private $smartcatClient;

    public function __construct(HubClientInterface $hubClient, SmartcatClientInterface $smartcatClient)
    {
        $this->hubClient = $hubClient;
        $this->smartcatClient = $smartcatClient;
    }

    /**
     * @param array $postIds
     * @return SmartcatProjectFactory
     */
    public function setPostIds(array $postIds): SmartcatProjectFactory
    {
        $this->postIds = $postIds;
        return $this;
    }

    /**
     * @param string $sourceLocale
     * @return SmartcatProjectFactory
     */
    public function setSourceLocale(string $sourceLocale): SmartcatProjectFactory
    {
        $this->sourceLocale = $sourceLocale;
        return $this;
    }

    /**
     * @param array $targetLocales
     * @return SmartcatProjectFactory
     */
    public function setTargetLocales(array $targetLocales)
    {
        $this->targetLocales = $targetLocales;
        return $this;
    }

    /**
     * @param string|null $workflowStage
     * @return SmartcatProjectFactory
     */
    public function setWorkflowStage($workflowStage): SmartcatProjectFactory
    {
        $this->workflowStage = $workflowStage;
        return $this;
    }

    /**
     * @param string|null $deadline
     * @return SmartcatProjectFactory
     */
    public function setDeadline($deadline): SmartcatProjectFactory
    {
        $this->deadline = $deadline;
        return $this;
    }

    /**
     * @param string $id
     * @return SmartcatProjectFactory
     */
    public function setId(string $id): SmartcatProjectFactory
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function create(): string
    {
        /** @var array|SmartcatWpError $response */
        $response = $this->hubClient->createProject(
            $this->getProjectName(),
            $this->sourceLocale,
            $this->targetLocales,
            $this->getWorkflowStages()
        );

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $this->setId($response['id']);
        sc_log()->info("Created Smartcat project: {$response['id']}");


        if ($this->workflowStage !== 'manual') {
            sc_log()->info('Started installing machine translation settings in Smartcat project');
            $this->setupMT();
        }

        return $this->id;
    }

    private function setupMT()
    {
        $selectedMt = NULL;

        /** @var SmartcatWpError|array $project */
        $project = $this->smartcatClient->getProject($this->id);

        if (is_wp_error($project)) {
            throw new \Exception($project->get_error_message());
        }

        /** @var SmartcatWpError|array $availableProjectMT */
        $availableProjectMT = $this->smartcatClient->availableProjectMT($this->id);

        if (is_wp_error($availableProjectMT)) {
            throw new \Exception($availableProjectMT->get_error_message());
        }

        $targetLocales = $project['targetLanguages'];

        $intelligentRouting = array_filter($availableProjectMT, function ($mt) {
            return $mt['Id'] === 'engine:Intelligent Routing';
        });

        $intelligentRouting = $intelligentRouting[0] ?? null;

        if (
            !is_null($intelligentRouting) &&
            count($intelligentRouting['Languages']) === count($targetLocales)
        ) {
            $selectedMt = $intelligentRouting;
        }

        if (is_null($selectedMt)) {
            $googleNMT = array_filter($availableProjectMT, function ($mt) {
                return $mt['Id'] === 'engine:Google NMT';
            });

            $googleNMT = array_shift($googleNMT);

            if (
                !is_null($googleNMT) &&
                count($googleNMT['Languages']) === count($targetLocales)
            ) {
                $selectedMt = $googleNMT;
            }
        }

        if (is_null($selectedMt)) {
            foreach ($availableProjectMT as $mt) {
                if (count($mt['Languages']) === count($targetLocales)) {
                    $selectedMt = $mt;
                    break;
                }
            }
        }

        if (!is_null($selectedMt)) {
            /** @var SmartcatWpError|array $project */
            $res = $this->smartcatClient->setupMT($this->id, [
                $selectedMt
            ]);

            if (is_wp_error($res)) {
                throw new \Exception($res->get_error_message());
            }

        } else {
            smartcat_logger()->warn("Failed to setup MT engine to project $this->id", [
                'targetLocales' => $targetLocales,
                'smartcatProjectId' => $this->id,
                'availableProjectMT' => $availableProjectMT
            ]);
        }

        // adding pre translation rules

        $rule = [
            'ruleType' => 'MT',
            'order' => 1
        ];

        $translationStage = array_filter($project['workflowStages'], function ($stage) {
            return $stage['stageType'] === 'translation';
        });

        $translationStage = array_shift($translationStage);

        if (!is_null($translationStage)) {
            $rule['confirmAtWorkflowStep'] = $translationStage['id'];
        } else {
            smartcat_logger()->warn('Failed finding translation workflow step. Pretransaltion rule will be without automatic confirmation');
        }

        /** @var SmartcatWpError|array $project */
        $res = $this->smartcatClient->addPreTranslationRules($this->id, [
            $rule
        ]);

        if (is_wp_error($res)) {
            throw new \Exception($res->get_error_message());
        }

    }

    private function getProjectName(): string
    {
        $post = get_post($this->postIds[0]);
        $andMore = count($this->postIds) > 1 ? ' and more' : '';
        $postTitle = $post->post_title;

        if (strlen($postTitle) > 40) {
            $andMore = '';
            $postTitle = mb_substr($postTitle, 0, 40) . '...';
        }

        return "Translation request ($postTitle" . "$andMore)";
    }

    private function getWorkflowStages(): array
    {
        return SC_PROJECT_WORKFLOW_STAGES[$this->workflowStage];
    }
}
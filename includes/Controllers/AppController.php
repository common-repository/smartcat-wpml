<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Controllers\Traits\HasTranslationRequest;

class AppController extends Controller
{
    use HasTranslationRequest;

    public function __construct()
    {
        $this->initTranslationRequest();
    }

    public function createTranslationRequest()
    {
        try {
            $translationRequestId = $this->translationRequest
                ->setPostIds($_POST['postId'])
                ->setSourceLocale($_POST['sourceLanguage'])
                ->setTargetLocales($_POST['targetLanguages'])
                ->setSmartcatProjectId($_POST['projectId'])
                ->setDeadline($_POST['deadline'])
                ->setComment($_POST['comment'] ?? NULL)
                ->setWorkflowStage($_POST['workflowStage'] ?? NULL)
                ->create();
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk(['id' => $translationRequestId]);
    }

    public function exportTranslations()
    {
        try {
            $this->translationRequest->exportTranslations(
                $_POST['postIds'] ?? NULL,
                $_POST['translationRequestId'] ?? NULL
            );

        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk();
    }

    public function exportTranslationsByPostAndLocale()
    {
        try {
            $this->translationRequest->getTranslationsByPostAndLocale(
                $_POST['translationRequestId'] ?? NULL,
                $_POST['postId'] ?? NULL,
                $_POST['locale'] ?? NULL
            );

            $this->responseOk();
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }
    }

    public function updateSourceContent()
    {
        try {
            $this->translationRequest->updateSourceContent(
                $_POST['postId'],
                $_POST['locale'],
                $_POST['translationRequestId']
            );
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk();
    }

    public function addLanguageToTranslationRequest()
    {
        try {
            $this->translationRequest->addLanguage(
                $_POST['postId'],
                $_POST['translationRequestId'],
                $_POST['language']
            );
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk();
    }

    public function removeLanguageFromTranslationRequest()
    {
        try {
            $this->translationRequest->removeLanguage(
                $_POST['postId'],
                $_POST['language']
            );
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk();
    }

    public function removeTranslationRequest()
    {
        try {
            $this->translationRequest->remove($_POST['trId']);
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk();
    }

    public function removePostFromTranslationRequest()
    {
        try {
            $this->translationRequest->removePost(
                $_POST['postId'],
                $_POST['translationRequestId']
            );
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }

        $this->responseOk();
    }

    public function translationRequestInfo()
    {
        try {
            $this->responseOk(
                $this->translationRequest->info(
                    $_POST['translationRequestId']
                )
            );
            return;
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }
    }

    public function getProjects()
    {
        try {
            $projects = smartcat_api()
                ->getProjectsList(
                    $_POST['limit'],
                    $_POST['offset'],
                    $_POST['projectName'] ?? ''
                );

            $this->responseOk(
                array_map(function ($p) {
                    return $p->toArray();
                }, $projects)
            );
            return;
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ], null, $exception);

            $this->responseFailed($exception->getMessage());
            return;
        }
    }
}

<?php

namespace Smartcat\Includes\Services\App;

use Smartcat\Includes\Services\App\Includes\DocumentsQueryBuilder;
use Smartcat\Includes\Services\App\Models\Document;

class Helpers
{
    use DocumentsQueryBuilder;

    public function postInTranslationRequest($postId): bool
    {
        $result = $this->addWhere('post_id', '%d', $postId)->get();

        return count($result) > 0;
    }

    /**
     * @return string|null
     */
    public function getPostTranslationRequest($postId)
    {
        $this->initDB();

        return $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT translation_request_id FROM {$this->table()} WHERE post_id = %d LIMIT 1", [$postId]
            )
        )[0] ?? NULL;
    }

    /**
     * @param $postId
     * @return Document[]
     */
    public function postDocuments($postId): array
    {
        $this->initDB();

        $documents = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table()} WHERE post_id = %d", [$postId]
            )
        );

        return array_map(function ($document) {
            return (new Document())
                ->setTranslatedPostId($document->translated_post_id)
                ->setPostId($document->post_id)
                ->setSmartcatDocumentId($document->smartcat_document_id)
                ->setSmartcatProjectId($document->smartcat_project_id)
                ->setLang($document->lang)
                ->setTranslationProgress($document->translation_progress)
                ->setCreatedAt($document->created_at)
                ->setTranslationRequestId($document->translation_request_id)
                ->setApiVersion($document->api_version)
                ->setComment($document->comment)
                ->setIsInvalidProject($document->is_invalid_project);
        }, $documents);
    }
}

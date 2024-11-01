<?php

namespace Smartcat\Includes\Services\App;

use Smartcat\Includes\Services\App\Includes\DocumentsQueryBuilder;
use Smartcat\Includes\Services\App\Models\Document;

class DocumentsService
{
    use DocumentsQueryBuilder;

    public function create(Document $document): DocumentsService
    {
        sc_log()->info('Adding a Document to the Database', $document->getStoredData());

        $this->insert($document->getStoredData());

        return $this;
    }

    public function whereTranslationRequestEquals($translationRequestId): DocumentsService
    {
        $this->addWhere('translation_request_id', '%s', $translationRequestId);

        return $this;
    }

    public function findDocumentByPostAndLocale($postId, $locale)
    {
        $this->addWhere('post_id', '%d', $postId);
        $this->addWhere('lang', '%s', $locale);

        $documents = $this->get();
        $document = array_shift($documents);

        if (!is_null($document)) {
            return $this->mapToModel($document);
        }

        return null;
    }

    /**
     * @return Document[]
     */
    public function fetch(): array
    {
        $documents = $this->get();

        return array_map(function ($document) {
            return $this->mapToModel($document);
        }, $documents);
    }

    public function updateTranslatedPostId($smartcatDocumentId, $postId)
    {
        $this->update(
            ['translated_post_id' => $postId],
            ['smartcat_document_id' => $smartcatDocumentId]
        );

        sc_log()->info("Setup translated post $postId to $smartcatDocumentId document in database");
    }

    public function removePostLanguage($postId, $locale)
    {
        $this->delete(['post_id' => $postId, 'lang' => $locale]);
    }

    public function removeAll($translationRequestId)
    {
        $this->delete(['translation_request_id' => $translationRequestId]);
    }

    public function removePost($postId, $translationRequestId)
    {
        $this->delete(['translation_request_id' => $translationRequestId, 'post_id' => $postId]);
    }

    /**
     * @param Document $document
     * @return void
     */
    public function save($document)
    {
        $this->update($document->getStoredData(), [
            'smartcat_document_id' => $document->getSmartcatDocumentId()
        ]);
    }

    /**
     * @param int|null $limit
     * @return array<Document>
     */
    public function getNonImportedItems(int $limit = null): array
    {
        $this->addWhere('is_imported', '%d', 0)
            ->limit($limit);

        $documents = $this->get();

        return array_map(function ($document) {
            return $this->mapToModel($document);
        }, $documents);
    }

    public function cancelImportStatus()
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'smartcat_documents',
            ['is_imported' => 0],
            ['is_imported' => 1]
        );
    }

    public function mapToModel($document): Document
    {
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
            ->setIsExported($document->is_exported)
            ->setIsImported($document->is_imported)
            ->setComment($document->comment)
            ->setIsInvalidProject($document->is_invalid_project);
    }
}

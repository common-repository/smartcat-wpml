<?php

namespace Smartcat\Includes\Services\App\Models;

use Smartcat\Includes\Services\App\Resources\TranslationRequestStatus;

class TranslationRequest
{
    /** @var string */
    private $id;

    /** @var Document[] */
    private $documents = [];

    /** @var string */
    private $sourceLocale;

    /** @var string */
    private $sourceLanguageName;

    /** @var \WP_Post[] */
    private $posts = [];

    /**F
     * @param string $id
     * @return TranslationRequest
     */
    public function setId(string $id): TranslationRequest
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Document[] $documents
     * @return TranslationRequest
     */
    public function setDocuments(array $documents): TranslationRequest
    {
        $this->documents = $documents;
        return $this;
    }

    public function postIds(): array
    {
        return array_map(function ($document) {
            return $document->getPostId();
        }, $this->documents);
    }

    public function getPostTargetLocales($postId)
    {
        $filteredDocuments = array_filter($this->documents, function ($document) use ($postId) {
            return $document->getPostId() == $postId;
        });

        return array_map(function ($document) {
            return $document->getLang();
        }, $filteredDocuments);
    }

    /**
     * @return string|null
     */
    public function smartcatProjectId()
    {
        if (isset($this->documents[0])) {
            return $this->documents[0]->getSmartcatProjectId();
        }

        return null;
    }

    public function createdAt()
    {
        if (isset($this->documents[0])) {
            return $this->documents[0]->getCreatedAt();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function sourceLocale()
    {
        if (isset($this->documents[0])) {
            return $this->sourceLocale ?? $this->sourceLocale = sc_wpml()->getPostLanguageCode(
                $this->documents[0]->getPostId()
            );
        }

        return null;
    }

    public function sourceLanguageName()
    {
        if (isset($this->documents[0])) {
            return $this->sourceLanguageName ?? $this->sourceLanguageName = sc_wpml()->getPostLanguageName(
                $this->documents[0]->getPostId()
            );
        }

        return null;
    }

    public function id()
    {
        return $this->id;
    }

    public function exists(): bool
    {
        return count($this->documents) > 0;
    }

    public function name(): string
    {
        return 'Translation request from ' . (new \DateTime($this->createdAt()))->format('Y/m/d');
    }

    public function posts(): array
    {
        if (!empty($this->posts)) {
            return $this->posts;
        }

        foreach ($this->documents as $document) {
            if (!isset($this->posts[$document->getPostId()])) {
                $this->posts[$document->getPostId()] = get_post($document->getPostId());
            }
        }

        return $this->posts;
    }

    public function postList(): array
    {
        $this->posts();

        return array_map(function ($post) {
            return [
                'id' => $post->ID,
                'name' => $post->post_title
            ];
        }, $this->posts);
    }

    public function postsMinifiedList(): array
    {
        $posts = [];

        foreach ($this->posts() as $post) {
            $posts[$post->ID] = $post->post_title;
        }

        return $posts;
    }

    public function postHasLocale($postId, $locale): bool
    {
        $filteredDocuments = array_filter($this->documents, function ($document) use ($postId, $locale) {
            return $document->getPostId() == $postId
                && $document->getLang() == $locale;
        });

        return count($filteredDocuments) > 0;
    }

    public function documents(): array
    {
        return $this->documents;
    }

    public function arrayableDocuments(): array
    {
        $documents = [];

        foreach ($this->documents as $document) {
            if (!isset($documents[$document->getPostId()])) {
                $documents[$document->getPostId()] = [
                    'postId' => $document->getPostId(),
                    'languages' => []
                ];
            }

            $documents[$document->getPostId()]['languages'][] = [
                'locale' => $document->getLang(),
                'translatedPostId' => $document->getTranslatedPostId(),
                'progress' => $document->getTranslationProgress(),
                'smartcatDocumentId' => $document->getSmartcatDocumentId()
            ];
        }

        return array_values($documents);
    }

    public function status(): TranslationRequestStatus
    {
        $status = new TranslationRequestStatus();
        $status->setType('Sent');

        $totalProgress = count($this->documents) * 100;
        $currentProgress = 0;

        foreach ($this->documents as $document) {
            $currentProgress += $document->getTranslationProgress();
        }

        if ($currentProgress > 0 && $currentProgress < $totalProgress) {
            $status->setType('In progress');
        } else if ($currentProgress >= $totalProgress) {
            $status->setType('Completed');
        }

        $status->setProgress(
            $status->getType() === 'In progress'
                ? round(($currentProgress / $totalProgress) * 100)
                : NULL
        );

        return $status;
    }

    public function comment()
    {
        $comment = null;

        foreach ($this->documents as $document) {
            $comment = !empty($document->getComment())
                ? $document->getComment()
                : $comment;
        }

        return $comment;
    }

    public function targetLocales(): array
    {
        $locales = [];

        foreach ($this->documents as $document) {
            if (!in_array($document->getLang(), $locales)) {
                $locales[] = $document->getLang();
            }
        }

        return $locales;
    }

    public function targetLanguageNames(): array
    {
        $languages = [];
        $wpmlLanguages = sc_wpml()->getActiveLanguages();
        foreach ($this->documents as $document) {
            foreach ($wpmlLanguages as $wpmlLanguage) {
                if ($wpmlLanguage['code'] === $document->getLang()) {
                    $languages[$document->getLang()] = $wpmlLanguage['translated_name'];
                    break;
                }
            }
        }
        return $languages;
    }

    public function isInvalidProject(): bool
    {
        $isInvalid = false;
        foreach ($this->documents as $document) {
            if($document->isInvalidProject()){
                $isInvalid = true;
            }
        }

        return $isInvalid;
    }
}

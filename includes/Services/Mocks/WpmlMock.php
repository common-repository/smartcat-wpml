<?php

namespace Smartcat\Includes\Services\Mocks;

use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class WpmlMock implements WpmlInterface
{
    public function switchLang(string $lang)
    {
        // TODO: Implement switchLang() method.
    }

    public function getTrid(int $postId, string $type = 'post_post')
    {
        // TODO: Implement getTrid() method.
    }

    public function getTranslations(int $trid, string $type = 'post_post'): array
    {
        return [];
    }

    public function getTargetElementId(int $originalPostId, string $lang, string $type = 'post_post')
    {
        // TODO: Implement getTargetElementId() method.
    }

    public function addTranslation(int $elementId, int $originalElementId, string $targetLang, string $sourceLang, string $type = 'post_post')
    {
        // TODO: Implement addTranslation() method.
    }

    public function getIdsBySourceElements(array $ids, array $languages): array
    {
        return [];
    }

    public function getElementLanguages(int $postId, array $exceptions = [], string $type = 'post_post'): array
    {
        return [];
    }

    public function getActiveLocales(): array
    {
        return [];
    }

    public function getPostLocale(int $postId)
    {
        // TODO: Implement getPostLocale() method.
    }

    public function getStrings(string $language, array $domains = []): array
    {
        return [];
    }

    public function registerString(string $domain, string $name, string $language, $value)
    {
        // TODO: Implement registerString() method.
    }

    public function getPostLanguageCode($postId)
    {
        // TODO: Implement getPostLanguageCode() method.
    }

    public function getPostLanguageName($postId)
    {
        // TODO: Implement getPostLanguageName() method.
    }

    public function getActiveLanguages(): array
    {
        return [];
    }
}
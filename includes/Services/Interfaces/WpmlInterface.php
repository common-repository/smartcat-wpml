<?php

namespace Smartcat\Includes\Services\Interfaces;

interface WpmlInterface
{
    public function switchLang(string $lang);

    public function getTrid(int $postId, string $type = 'post_post');

    public function getTranslations(int $trid, string $type = 'post_post'): array;

    /**
     * @deprecated Use getTranslationId()
     *
     * @param int $originalPostId
     * @param string $lang
     * @param string $type
     * @return mixed
     */
    public function getTargetElementId(int $originalPostId, string $lang, string $type = 'post_post');

    public function getTranslationId(int $originalId, string $locale, string $typePrefix = 'post');

    public function addTranslation(int $elementId, int $originalElementId, string $targetLang, string $sourceLang, string $type = 'post_post');

    public function getIdsBySourceElements($ids, array $languages): array;

    public function getElementLanguages(int $postId, array $exceptions = [], string $type = 'post_post'): array;

    public function getActiveLocales(): array;

    public function getPostLocale(int $postId);

    public function getStrings(string $language, array $domains = []): array;

    public function registerString(string $domain, string $name, string $language, $value);

    public function getTranslatedPostIds(array $types, array $languages): array;

    public function getPostLanguageCode($postId);

    public function getPostLanguageName($postId);

    public function getActiveLanguages(): array;
}
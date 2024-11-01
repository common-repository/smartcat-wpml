<?php

namespace Smartcat\Includes\Services\App;

use Smartcat\Includes\Services\API\Models\TranslatedItem;
use Smartcat\Includes\Services\App\Resources\ExportedData;
use Smartcat\Includes\Services\Elementor\Document;
use Smartcat\Includes\Services\Interfaces\MetadataServiceInterface;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Posts\BlocksService;
use Smartcat\Includes\Services\Elementor\ElementorService;
use Smartcat\Includes\Services\WPML\Models\WpmlContentItem;

class ContentService
{
    /** @var array */
    private $items = [];

    /** @var MetadataServiceInterface */
    private $metadataService;

    /** @var WpmlContentItem[]|TranslatedItem[]|ExportedData */
    private $exportedData = [];

    /** @var \WP_Post */
    private $originalPost;

    /** @var \WP_Post */
    private $translatedPost;

    /** @var string */
    private $currentLocale;

    /** @var PostsDatabaseInterface */
    private $postsDatabase;

    /** @var ElementorService */
    private $elementorService;

    public function __construct(MetadataServiceInterface $metadataService, PostsDatabaseInterface $postsDatabase, ElementorService $elementorService)
    {
        $this->metadataService = $metadataService;
        $this->postsDatabase = $postsDatabase;
        $this->elementorService = $elementorService;
    }

    public function parse(\WP_Post $post, $apiVersion): array
    {
        if (!sc_check_option('sc_disable_wpml_strings_register')) {
            sc_wpml()->pb()->registerAllStrings($post);
        }

        if ($apiVersion === 'v1') {
            $items = $this->getContentToImportV1($post);
        } else {
            $items = sc_wpml()->content()->get($post->ID);
            $items = $this->mapToLocJson($items);
        }

        sc_log()->info("Parse content from post $post->ID. API ($apiVersion) will be using");

        return $items;
    }

    /**
     * @param WpmlContentItem[] $items
     * @return array
     */
    private function mapToLocJson(array $items): array
    {
        $nonTranslatableItems = [];
        $mappedItems = [];

        foreach ($items as $item) {
            if ($item->isTranslatable()) {
                $mappedItems[] = $item->toLocJsonArray();
            } else {
                $nonTranslatableItems[] = $item;
            }
        }

        return [
            'items' => $mappedItems,
            'properties' => $this->metaDataJson($nonTranslatableItems)
        ];
    }

    /**
     * @param WpmlContentItem[] $nonTranslatableItems
     * @return string
     */
    private function metaDataJson(array $nonTranslatableItems): string
    {
        $items = array_map(function ($item) {
            return $item->toArray();
        }, $nonTranslatableItems);

        return json_encode($items);
    }

    /**
     * @param $originalPostId
     * @param $locale
     * @param array $items
     * @param array $meta
     * @param $apiVersion
     * @return int
     */
    public function import($originalPostId, $locale, array $items, array $meta, $apiVersion): int
    {
        sc_log()->info("Import received translations into WordPress. API ($apiVersion) will be using", [
            'originalPostId' => $originalPostId,
            'locale' => $locale,
            'items' => $items,
            'meta' => $meta
        ]);

        $this->originalPost = get_post($originalPostId);
        $this->currentLocale = $locale;

        $this->duplicateOrOverwritePost();

        $this->exportedData = $apiVersion === 'v1'
            ? $this->prepareExportedDataV1($items)
            : $this->prepareExportedDataVLatest($items, $meta);

        sc_log()->info("Prepared exported data for post {$this->translatedPost->ID} ($locale)", [
            'locale' => $locale,
            'translatedPostId' => $this->translatedPost->ID,
            'data' => array_map(function ($item) {
                return $item->toArray();
            }, $this->exportedData)
        ]);

        if ($apiVersion === 'v1') {
            return $this->importContentV1();
        } else {
            return sc_wpml()
                ->content()
                ->save(
                    $this->originalPost,
                    $this->translatedPost->ID,
                    $this->exportedData,
                    $this->currentLocale
                );
        }
    }

    /**
     * @param array $items
     * @param array $meta
     * @return WpmlContentItem[]
     */
    private function prepareExportedDataVLatest(array $items, array $meta): array
    {
        $preparedItems = [];

        foreach ($items as $item) {
            $preparedItems[] = new WpmlContentItem(
                $item['id'], 1,
                $item['source'],
                $item['translation'],
                $item['properties']['format'] ?? null,
                $item['properties']['wrap-tag'] ?? null
            );
        }

        foreach ($meta as $item) {
            $preparedItems[] = new WpmlContentItem(
                $item['key'],
                (int)$item['isTranslatable'],
                $item['sourceData'],
                $item['translatedData'],
                $item['format'],
                $item['wrapTag']
            );
        }

        return $preparedItems;
    }

    private function mapToTranslatedItems(array $items): array
    {
        return array_map(function ($item) {
            return (new TranslatedItem())
                ->setId($item['id'])
                ->setName($item['context'])
                ->setSource($item['source'])
                ->setTranslation($item['translation'])
                ->setContext($item['properties']['context']);
        }, $items);
    }

    private function addItem(string $id, $content, string $name, string $context, string $format = 'plain')
    {
        $this->items[] = [
            'id' => $id,
            'sourceText' => $content,
            'format' => $format,
            'context' => $name,
            'properties' => [
                'context' => $context,
            ],
            'existingTranslation' => ''
        ];
    }

    private function storeCustomFields(array $data)
    {
        foreach ($data as $key => $value) {
            $this->addItem($key, $value['content'], 'Custom field', 'metadata', 'auto');
        }
    }

    private function storeElementorData(array $data)
    {
        foreach ($data as $element) {
            $this->addItem($element['key'], $element['content'], 'Elementor', 'elementor', 'auto');
        }
    }

    private function storeGutenbergData(array $data)
    {
        foreach ($data as $item) {
            $this->addItem($item['key'], $item['content'], 'Gutenberg editor', 'block', 'auto');
        }
    }

    private function bakeryDecode(array $blocks): array
    {
        return array_map(function ($block) {
            $block['content'] = sc_bakery_builder()->decode($block['content']);
            return $block;
        }, $blocks);
    }

    private function isTranslationExists(): bool
    {
        return $this->tryFindTranslatedPost();
    }

    private function tryFindTranslatedPost(): bool
    {
        $translationId = sc_wpml()->getTranslationId(
            $this->originalPost->ID, $this->currentLocale
        );

        if (is_null($translationId)) {
            return false;
        }

        $post = get_post($translationId);

        if (is_null($post)) {
            return false;
        }

        $this->translatedPost = $post;

        return true;
    }

    private function duplicateOrOverwritePost()
    {
        /** @var \SitePress $sitepress */
        global $sitepress;

        $id = sc_wpml()->makeDuplicate(
            $this->originalPost->ID,
            $this->currentLocale
        );

        $postStatus = !$sitepress->get_setting('translated_document_status')
            ? 'draft'
            : $this->originalPost->post_status;

        wp_update_post([
            'ID' => $id,
            'post_status' => $postStatus
        ]);

        sc_log()->info("Duplicate {$this->originalPost->ID} post created to locale $this->currentLocale");

        $this->translatedPost = get_post($id);
    }

    private function getExportedDataValue(string $key)
    {
        $filteredData = array_filter($this->exportedData, function ($item) use ($key) {
            return $item->getId() === $key;
        });

        $item = array_shift($filteredData);

        return $item instanceof TranslatedItem ? $item->getTranslation() : '';
    }

    private function parseGutenbergContent(): string
    {
        $gutenbergBlocks = array_filter($this->exportedData, function ($item) {
            return $item->getContext() === 'block';
        });

        $preparedBlocks = array_map(function ($item) {
            return [
                'key' => $item->getId(),
                'content' => sc_built_with_bakery($this->originalPost->ID)
                    ? sc_bakery_builder()->encode($item->getTranslation())
                    : $item->getTranslation()
            ];
        }, $gutenbergBlocks);

        return BlocksService::applyTranslatedBlocks(
            $this->originalPost->post_content,
            $preparedBlocks
        );
    }

    private function parseMetadata(): array
    {
        $customFields = array_filter($this->exportedData, function ($item) {
            return $item->getContext() === 'metadata';
        });

        $metadata = [];

        foreach ($customFields as $item) {
            $metadata[$item->getId()] = [
                'content' => $item->getTranslation()
            ];
        }

        return $metadata;
    }

    private function parseElementorData(): array
    {
        $elementorItems = array_filter($this->exportedData, function ($item) {
            return $item->getContext() === 'elementor';
        });

        $preparedData = array_map(function ($item) {
            return [
                'key' => $item->getId(),
                'context' => $item->getContext(),
                'content' => $item->getTranslation(),
            ];
        }, $elementorItems);

        return array_values($preparedData);
    }

    private function prepareExportedDataV1($items): ExportedData
    {
        $this->exportedData = $this->mapToTranslatedItems($items);

        return new ExportedData(
            $this->getExportedDataValue('post_title'),
            $this->getExportedDataValue('post_slug'),
            $this->parseGutenbergContent(),
            $this->parseMetadata(),
            $this->parseElementorData()
        );
    }

    private function getContentToImportV1(\WP_Post $post): array
    {
        $this->items = [];

        $this->addItem('post_title', $post->post_title, 'Post title', 'title');
        $this->addItem('post_slug', urldecode($post->post_name), 'Post slug', 'slug');

        $mayBeElementorPost = $this->elementorService->checkPost($post->ID);

        if ($mayBeElementorPost->isBuiltWithElementor() && !$mayBeElementorPost->hasErrors()) {
            $this->storeElementorData($this->elementorService->data());
        } else {
            $blocks = BlocksService::parseContentBlocks($post->post_content);

            if (sc_built_with_bakery($post->ID)) {
                $blocks = $this->bakeryDecode($blocks);
            }

            $this->storeGutenbergData($blocks);
        }

        $this->storeCustomFields(
            $this->metadataService->getFields($post->ID)
        );

        return [
            'items' => $this->items,
            'properties' => '[]'
        ];
    }

    private function importContentV1(): int
    {
        $data = $this->exportedData;

        delete_post_meta($this->translatedPost->ID, '_icl_lang_duplicate_of');

        wp_update_post([
            'ID' => $this->translatedPost->ID,
            'post_title' => $data->getTitle(),
            'post_name' => $data->getSlug(),
            'post_status' => 'draft'
        ]);

        $postCheckResult = $this->elementorService->checkPost($this->originalPost->ID);

        if ($postCheckResult->isBuiltWithElementor() && !$postCheckResult->hasErrors()) {
            sc_log()->info("Detected Elementor in original post {$this->originalPost->ID}");

            (new Document($this->translatedPost->ID))->save(
                $this->elementorService->elements()
            );

            sc_log()->info("Translated post ({$this->translatedPost->ID}) content was updated by original post {$this->originalPost->ID}", $this->elementorService->elements());

            $targetElementorPost = new Document($this->translatedPost->ID);
            $targetElementorPost->updateElementsData($data->getElementor());

            sc_log()->info("Inserted translated Elementor content to target post ({$this->translatedPost->ID})", [
                'translatedPostId' => $this->translatedPost->ID,
                'data' => $data->getElementor(),
                'locale' => $this->currentLocale
            ]);
        } else {
            $this->postsDatabase->updatePostContent(
                $this->translatedPost->ID,
                $data->getGutenbergContent()
            );

            sc_log()->info("Inserted translated Gutenberg content to target post ({$this->translatedPost->ID})", [
                'translatedPostId' => $this->translatedPost->ID,
                'data' => $data->getGutenbergContent(),
                'locale' => $this->currentLocale
            ]);
        }

        $this->metadataService->addMetaDataToPost(
            $this->translatedPost->ID,
            $data->getMetadata()
        );

        sc_log()->info("Inserted translated metadata to target post ({$this->translatedPost->ID})", [
            'translatedPostId' => $this->translatedPost->ID,
            'data' => $data->getMetadata(),
            'locale' => $this->currentLocale
        ]);

        return $this->translatedPost->ID;
    }

}

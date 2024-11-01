<?php

namespace Smartcat\Includes\Services\Elementor;

use Elementor\Plugin;

class Document
{
    private $postId;

    /**
     * @var Collection
     */
    private $elementsCollection;

    /**
     * @var \Elementor\Core\Base\Document
     */
    private $elementorDocument;

    /**
     * @var \Elementor\Plugin|null
     */
    private $elementorInstance;

    /**
     * @var \WP_Post
     */
    private $post;

    public function __construct($postId = NULL)
    {
        $this->elementorInstance = \Elementor\Plugin::$instance;

        if (!is_null($postId)) {
            $this->setPostId($postId);
        }
    }

    /**
     * @param mixed $postId
     * @return Document
     */
    public function setPostId($postId): Document
    {
        $this->postId = $postId;

        $this->post = get_post($postId);

        $this->elementorDocument = $this->elementorInstance
            ->documents
            ->get($postId);

        $this->elementsCollection = (new Collection())
            ->make(
                $this->elementorDocument->get_elements_data()
            );

        return $this;
    }

    public function isBuiltWithElementor(): bool
    {
        return $this->elementorDocument->is_built_with_elementor();
    }

    public function getElements(bool $useArray = false): array
    {
        return $useArray
            ? $this->elementsCollection->toArray()
            : $this->elementsCollection->get();
    }

    public function getData(): array
    {
        $data = $this->elementsCollection->getTranslatableJson();

        $filteredData = array_filter($data, function ($item) {
            return !empty($item['value']);
        });

        $filteredData = array_values($filteredData);

        return array_map(function ($item) {
            return [
                'key' => $item['key'],
                'content' => $item['value'],
                'context' => "elementor.{$item['settingsKey']}"
            ];
        }, $filteredData);
    }

    public function updateElementsData(array $items)
    {
        foreach ($items as $item) {
            if ($this->isElementorItem($item)) {
                $elementKey = new ElementKey($item['key']);
                $this->elementsCollection->updateElementSettings(
                    $elementKey->getWidgetId(),
                    $elementKey->getMainKey(),
                    $item['content'],
                    $elementKey->getListItemId(),
                    $elementKey->getListItemKey()
                );
            }
        }

        $this->save();
    }

    private function isElementorItem($item): bool
    {
        $context = $item['context'] ?? NULL;

        return !is_null($context) && sc_str_contains($context, 'elementor');
    }

    public function save(array $externalData = [])
    {
        $currentUserId = get_current_user_id() === 0
            ? $this->post->post_author
            : get_current_user_id();

        set_current_user($currentUserId);

        if (!empty($externalData)) {
            $collection = (new Collection())->make($externalData);
            $collection->handleTemplates(
                smartcat_wpml()->getPostLocale($this->postId)
            );
            $editorData = $collection->toArray();
        } else {
            $this->elementsCollection->handleTemplates(
                smartcat_wpml()->getPostLocale($this->postId)
            );
            $editorData = $this->elementsCollection->toArray();
        }

        $this->elementorDocument->save([
            'elements' => $editorData,
        ]);
    }
}
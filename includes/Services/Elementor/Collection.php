<?php

namespace Smartcat\Includes\Services\Elementor;

use Smartcat\Includes\Services\Elementor\Models\BaseModel;
use Smartcat\Includes\Services\Elementor\Models\Neutral;

class Collection
{
    /**
     * @var BaseModel[]
     */
    private $collection = [];

    public function make(array $elementorData = []): Collection
    {
        foreach ($elementorData as $element) {
            if ($item = $this->serialize($element)) {
                $this->collection[] = $item;
            }
        }

        return $this;
    }

    /**
     * @param $element
     * @return BaseModel
     */
    private function serialize($element): BaseModel
    {
        $neutralInstance = new Neutral($element);

        $modelClass = $neutralInstance->isWidget()
            ? $this->getElementClass($neutralInstance->getWidgetType())
            : Neutral::class;

        /** @var BaseModel $model */
        $model = new $modelClass($element);

        $modelElements = [];

        foreach ($element['elements'] as $el) {
            $modelElements[] = $this->serialize($el);
        }

        $model->setElements($modelElements);

        return $model;
    }

    /**
     * @return BaseModel[]
     */
    public function get(): array
    {
        return $this->collection;
    }

    /**
     * @return BaseModel[]
     */
    public function getTranslatableElements(): array
    {
        $collection = [];

        foreach ($this->get() as $el) {
            $collection = array_merge($collection, $this->findTranslatableElements($el));
        }

        return $collection;
    }

    /**
     * @param BaseModel $element
     * @return array
     */
    public function findTranslatableElements(BaseModel $element): array
    {
        $elements = [];

        if ($element->isTranslatable()) {
            $elements[] = $element;
        }

        foreach ($element->getElements() as $childElement) {
            $elements = array_merge($elements, $this->findTranslatableElements($childElement));
        }

        return $elements;
    }

    public function getTranslatableJson(): array
    {
        $values = [];

        foreach ($this->getTranslatableElements() as $translatableElement) {
            foreach ($translatableElement->toTranslatableData() as $datum) {
                $values[] = $datum->toArray();
            }
        }

        return $values;
    }

    public function updateElementSettings(string $widgetId, string $mainKey, $value, $listItemId = NULL, $listItemKey = NULL)
    {
        foreach ($this->collection as $element) {
            $this->updateElement($element, $widgetId, $mainKey, $value, $listItemId, $listItemKey);
        }
    }

    /**
     * @param BaseModel $element
     * @param string $widgetId
     * @param string $mainKey
     * @param $value
     * @param $listItemId
     * @param $listItemKey
     * @return bool|void
     */
    private function updateElement(BaseModel $element, string $widgetId, string $mainKey, $value, $listItemId = NULL, $listItemKey = NULL)
    {
        if ($element->getId() === $widgetId) {
            $element->updateSettings($mainKey, $value, $listItemId, $listItemKey);
            return true;
        }

        foreach ($element->getElements() as $item) {
            $this->updateElement($item, $widgetId, $mainKey, $value, $listItemId, $listItemKey);
        }
    }

    /**
     * @param string $widgetId
     * @return BaseModel|null
     */
    public function findElementByWidgetId(string $widgetId)
    {
        foreach ($this->getTranslatableElements() as $translatableElement) {
            if ($translatableElement->getId() === $widgetId) {
                return $translatableElement;
            }
        }

        return NULL;
    }

    public function handleTemplates($locale)
    {
        foreach ($this->collection as $item) {
            $this->updateTemplateId($item, $locale);
        }
    }

    private function updateTemplateId(BaseModel $item, $locale)
    {
        if ($item->isTemplate()) {
            $item->updateTemplateId($locale);
        } else {
            $item->updateTemplateShortcode($locale);
        }

        foreach ($item->getElements() as $element) {
            $this->updateTemplateId($element, $locale);
        }
    }

    public function toArray()
    {
        $array = [];

        foreach ($this->collection as $item) {
            $array[] = $this->convertElementToArray($item);
        }

        return $array;
    }

    private function convertElementToArray(BaseModel $element)
    {
        $model = $element->toArray();

        $modelElements = [];

        foreach ($element->getElements() as $el) {
            $modelElements[] = $this->convertElementToArray($el);
        }

        $model['elements'] = $modelElements;

        return $model;
    }

    private function getElementClass(string $type): string
    {
        return SMARTCAT_ELEMENTOR_TYPES[$type] ?? Neutral::class;
    }
}
<?php

namespace Smartcat\Includes\Services\Elementor;

class ElementKey
{
    /**
     * @var string
     */
    private $elementKey;

    /**
     * @var string
     */
    private $widgetId;

    /**
     * @var string
     */
    private $mainKey;

    /**
     * @var null|string
     */
    private $listItemId;

    /**
     * @var null|string
     */
    private $listItemKey;

    public function __construct(string $key)
    {
        $this->elementKey = $key;
        $this->parseKey();
    }

    /**
     * @return string
     */
    public function getWidgetId(): string
    {
        return $this->widgetId;
    }

    /**
     * @return string
     */
    public function getMainKey(): string
    {
        return $this->mainKey;
    }

    /**
     * @return string|null
     */
    public function getListItemId()
    {
        return $this->listItemId;
    }

    /**
     * @return string|null
     */
    public function getListItemKey()
    {
        return $this->listItemKey;
    }

    public function isListItem(): bool
    {
        // TODO: to regex
        return sc_str_contains($this->elementKey, '[');
    }

    private function parseKey()
    {
        $this->widgetId = $this->parseWidgetId();
        $this->mainKey = $this->parseMainKey();
        $this->listItemId = $this->parseListItemId();
        $this->listItemKey = $this->parseListItemKey();
    }

    private function parseListItemKey()
    {
        if ($this->isListItem()) {
            $explodedKey = explode('[', $this->elementKey)[1];
            return explode(']', $explodedKey)[0];
        }

        return NULL;
    }

    private function parseListItemId()
    {
        if ($this->isListItem()) {
            $explodedKey = explode('.', $this->elementKey)[1];
            return explode('[', $explodedKey)[0];
        }

        return NULL;
    }

    private function parseWidgetId(): string
    {
        return explode('(', $this->elementKey)[0];
    }

    private function parseMainKey(): string
    {
        $explodedKey = explode('(', $this->elementKey)[1];

        if (!$this->isListItem()) {
            return explode(')', $explodedKey)[0];
        }

        return explode('.', $explodedKey)[0];
    }
}
<?php

namespace Smartcat\Includes\Views\UI\Components;

abstract class Component
{
    private $isHide = false;

    private $customClasses = [];

    private $elementId = '';

    private $elementName = '';

    private $isDisabled = false;

    private $css = [];

    private $tooltipData = null;

    abstract public function render();

    /**
     * @param bool $hide
     * @return Component
     */
    public function hide(bool $hide = true): self
    {
        $this->isHide = $hide;
        return $this;
    }

    public function classes(...$classes): self
    {
        $this->customClasses = $classes;
        return $this;
    }

    /**
     * @param string $elementId
     * @return Component
     */
    public function id(string $elementId): self
    {
        $this->elementId = $elementId;
        return $this;
    }

    /**
     * @param bool $isDisabled
     * @return Component
     */
    public function disabled(bool $isDisabled = true): Component
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * @param array $css
     * @return Component
     */
    public function css(array $css): Component
    {
        $this->css = $css;
        return $this;
    }

    /**
     * @param null $tooltip
     * @return Component
     */
    public function tooltipText($tooltip)
    {
        $this->tooltipData = $tooltip;
        return $this;
    }

    /**
     * @return null
     */
    protected function getTooltipData()
    {
        return $this->tooltipData;
    }

    protected function hasTooltip(): bool
    {
        return !empty($this->tooltipData);
    }

    protected function getCssString(): string
    {
        $css = '';

        foreach ($this->css as $key => $value) {
            $css .= "$key: $value;";
        }

        return $css;
    }

    /**
     * @return bool
     */
    protected function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * @return string
     */
    protected function getElementId(): string
    {
        return $this->elementId;
    }

    /**
     * @param string $elementName
     * @return Component
     */
    public function name(string $elementName): Component
    {
        $this->elementName = $elementName;
        return $this;
    }

    /**
     * @return string
     */
    protected function getElementName(): string
    {
        return $this->elementName;
    }

    protected function isHide(): bool
    {
        return $this->isHide;
    }

    protected function getCustomClasses(): array
    {
        return $this->customClasses;
    }

    protected function getCustomClassesString(): string
    {
        return implode(' ', $this->customClasses);
    }
}
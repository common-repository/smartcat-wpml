<?php

namespace Smartcat\Includes\Services\Elementor\Models;

use Smartcat\Includes\Services\Tools\JsonMagician;

abstract class BaseModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var BaseModel[]
     */
    private $elements;

    /**
     * @var bool|null
     */
    private $isInner = null;

    /**
     * @var string|null
     */
    private $widgetType = NULL;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setId($data['id'])
                ->setType($data['elType'])
                ->setSettings($data['settings'])
                ->setIsInner($data['isInner'] ?? NULL)
                ->setWidgetType($data['widgetType'] ?? NULL);
        }
    }

    /**
     * @param string $id
     * @return BaseModel
     */
    public function setId(string $id): BaseModel
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $type
     * @return BaseModel
     */
    public function setType(string $type): BaseModel
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param array $settings
     * @return BaseModel
     */
    public function setSettings(array $settings): BaseModel
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param BaseModel[] $elements
     * @return BaseModel
     */
    public function setElements(array $elements): BaseModel
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * @return BaseModel[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param bool|null $isInner
     * @return BaseModel
     */
    public function setIsInner($isInner): BaseModel
    {
        $this->isInner = $isInner;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isInner()
    {
        return $this->isInner;
    }

    /**
     * @param string|null $widgetType
     * @return BaseModel
     */
    public function setWidgetType($widgetType): BaseModel
    {
        $this->widgetType = $widgetType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * @return bool
     */
    public function isWidget(): bool
    {
        return !is_null($this->widgetType);
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return in_array(
            $this->getWidgetType(),
            $this->getElementorTypes()
        );
    }

    /**
     * @return ElementValue[]
     */
    public function toTranslatableData(): array
    {
        $data = [];

        foreach ($this->getTranslatableFields() as $translatableFieldKey) {
            $data = array_merge($data, $this->parseSettingsValue($translatableFieldKey));
        }

        return $data;
    }

    public function updateSettings(string $mainKey, $value, $listItemId = NULL, $listItemKey = NULL)
    {
        if (is_null($listItemId) && is_null($listItemKey)) {
            $this->settings[$mainKey] = $value;
        } else {
            $list = $this->settings[$mainKey];
            $updatedList = [];

            if (!empty($list) && is_array($list)) {
                foreach ($list as $item) {
                    if ($item['_id'] === $listItemId) {
                        $item[$listItemKey] = $value;
                    }

                    $updatedList[] = $item;
                }
            }

            $this->settings[$mainKey] = $updatedList;
        }
    }

    public function isTemplate(): bool
    {
        return $this->getWidgetType() === 'template';
    }

    public function isShortcode(): bool
    {
        return $this->getWidgetType() === 'shortcode';
    }

    public function updateTemplateId($locale)
    {
        $originalTemplateId = $this->getTemplateId();
        $templateType = get_post_type($originalTemplateId);

        $targetTemplateId = smartcat_wpml()->getTargetElementId(
            $originalTemplateId, $locale, "post_$templateType"
        );

        $settings = $this->getSettings();
        $settings['template_id'] = $targetTemplateId ?? $originalTemplateId;

        $this->setSettings($settings);
    }

    public function updateTemplateShortcode($locale)
    {
        $settings = $this->getSettings();
        $jsonMagic = new JsonMagician($settings);
        $settings = $jsonMagic->getJson(true);

        foreach ($settings as $key => $value) {
            if (is_string($value)) {

                $originalTemplateId = $this->parseTemplateIdFromShortcode($value);

                if (empty($originalTemplateId)) {
                    continue;
                }

                $templateType = get_post_type($originalTemplateId);

                $targetTemplateId = smartcat_wpml()->getTargetElementId(
                    $originalTemplateId, $locale, "post_$templateType"
                );

                if (empty($targetTemplateId)) {
                    $targetTemplateId = smartcat_wpml()->makeDuplicate($originalTemplateId, $locale);
                }

                $settings[$key] = str_replace($originalTemplateId, $targetTemplateId, $value);
            }
        }

        $settings = $jsonMagic->undot($settings);

        $this->setSettings($settings);
    }

    private function parseTemplateIdFromShortcode($content)
    {
        $content = $this->normalizeTemplateShortcode($content);

        $pattern = '/\[elementor-template\s+id=\"(?<id>\d+?)\"]/';
        preg_match($pattern, $content, $output);

        return $output['id'] ?? NULL;
    }

    private function normalizeTemplateShortcode($content)
    {
        $pattern = '/(?<prefix>\[elementor-template\s+id=&quot;)(?<id>\d+)(?<suffix>&quot;])/';
        $replacement = '[elementor-template id="$2"]';

        return preg_replace($pattern, $replacement, $content);
    }

    private function getTemplateId()
    {
        $settings = $this->getSettings();
        return $settings['template_id'];
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->getId(),
            'elType' => $this->getType(),
            'settings' => $this->getSettings(),
            'elements' => $this->getElements(),
        ];

        if (is_bool($this->isInner())) {
            $data['isInner'] = $this->isInner();
        }

        if ($this->isWidget()) {
            $data['widgetType'] = $this->getWidgetType();
        }

        return $data;
    }

    /**
     * @param string $settingsKey
     * @return ElementValue[]
     */
    private function parseSettingsValue(string $settingsKey): array
    {
        $items = [];

        if (!$this->isList($settingsKey)) {
            if (!empty($value = $this->getSettingsValue($settingsKey))) {
                $item = (new ElementValue())
                    ->setKey("{$this->getId()}($settingsKey)")
                    ->setSettingsKey($settingsKey)
                    ->setValue($value);
                $items[] = $item;
            }

            return $items;
        }

        $list = $this->getSettingsValue(
            $this->getSettingsListKey($settingsKey)
        );

        if (!empty($list) && is_array($list)) {
            foreach ($list as $value) {
                $listKey = $this->getSettingsListKey($settingsKey);
                $listValueKey = $this->getSettingsListValueKey($settingsKey);

                $item = (new ElementValue())
                    ->setKey("{$this->getId()}($listKey.{$value['_id']}[$listValueKey])")
                    ->setSettingsKey($settingsKey)
                    ->setValue($value[$listValueKey]);

                $items[] = $item;
            }
        }

        return $items;
    }

    private function getSettingsValue($settingsKey)
    {
        return $this->getSettings()[$settingsKey] ?? NULL;
    }

    private function isList(string $settingsKey): bool
    {
        return sc_str_contains($settingsKey, '.*.');
    }

    private function getSettingsListKey(string $settingsKey): string
    {
        return explode('.*.', $settingsKey)[0];
    }

    private function getSettingsListValueKey(string $settingsKey): string
    {
        return explode('.*.', $settingsKey)[1];
    }

    /**
     * @return string[]
     */
    private function getElementorTypes(): array
    {
        return array_keys(SMARTCAT_ELEMENTOR_TYPES);
    }

    abstract public function getTranslatableFields(): array;
}
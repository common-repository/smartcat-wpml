<?php

namespace Smartcat\Includes\Services\Elementor\Models;

class ElementValue
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $settingsKey;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setKey($data['key'])
                ->setSettingsKey($data['settingsKey'])
                ->setValue($data['value']);
        }
    }

    /**
     * @param mixed $key
     * @return ElementValue
     */
    public function setKey($key): ElementValue
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param mixed $value
     * @return ElementValue
     */
    public function setValue($value): ElementValue
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $settingsKey
     * @return ElementValue
     */
    public function setSettingsKey($settingsKey): ElementValue
    {
        $this->settingsKey = $settingsKey;
        return $this;
    }

    /**
     * @return array|string|string[]
     */
    public function getSettingsKey()
    {
        return str_replace('.*.', '.', $this->settingsKey);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'settingsKey' => $this->getSettingsKey(),
            'value' => $this->getValue(),
        ];
    }
}
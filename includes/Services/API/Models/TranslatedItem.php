<?php

namespace Smartcat\Includes\Services\API\Models;

class TranslatedItem
{
    /** @var numeric|string */
    private $id;

    /** @var string */
    private $name;

    /** @var null|string|numeric */
    private $source;

    /** @var null|string|numeric */
    private $translation;

    /** @var array */
    private $properties;

    /**
     * @param float|int|string $id
     * @return TranslatedItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return TranslatedItem
     */
    public function setName(string $name): TranslatedItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param float|int|string|null $source
     * @return TranslatedItem
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param float|int|string|null $translation
     * @return TranslatedItem
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * @param string $context
     * @return TranslatedItem
     */
    public function setContext(string $context): TranslatedItem
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return float|int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float|int|string|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return float|int|string|null
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'source' => $this->getSource(),
            'translation' => $this->getTranslation(),
            'properties' => $this->getProperties()
        ];
    }

    /**
     * @param array $properties
     * @return TranslatedItem
     */
    public function setProperties(array $properties): TranslatedItem
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
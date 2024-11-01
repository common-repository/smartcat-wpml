<?php

namespace Smartcat\Includes\Services\WPML\Models;

use \TranslationManagement;

class WpmlContentItem
{
    /** @var string */
    private $key;

    /** @var bool */
    private $isTranslatable;

    /** @var ?string */
    private $sourceData;

    /** @var ?string */
    private $translatedData;

    /** @var string */
    private $format;

    /** @var string */
    private $name;

    /** @var ?string */
    private $wrapTag;

    public function __construct($key, $isTranslatable, $sourceData, $translatedData, $format, $wrapTag, $decodeSourceData = false)
    {
        $this->key = $key;
        $this->isTranslatable = $isTranslatable;
        $this->setSourceData($key, $sourceData, $format, $decodeSourceData);
        $this->translatedData = $translatedData;
        $this->format = $format;
        $this->name = $this->name($key);
        $this->wrapTag = $wrapTag;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->isTranslatable == 1;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    private function name($key): string
    {
        if (sc_wpml()->isPackage($key)) {
            return sc_wpml()->getPackageKind(
                sc_wpml()->extractPackageId($key)
            );
        }

        if (sc_wpml()->isCustomField($key)) {
            return 'Custom field';
        }

        switch ($key) {
            case 'title':
                return 'Post title';
            case 'excerpt':
                return 'Post excerpt';
            case 'URL':
                return 'Post slug (url)';
            default:
                return 'Post content';
        }
    }

    /**
     * @param string $key
     * @return WpmlContentItem
     */
    public function setKey(string $key): WpmlContentItem
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param bool $isTranslatable
     * @return WpmlContentItem
     */
    public function setIsTranslatable(bool $isTranslatable): WpmlContentItem
    {
        $this->isTranslatable = $isTranslatable;
        return $this;
    }


    /**
     * @param string $format
     * @return WpmlContentItem
     */
    public function setFormat(string $format): WpmlContentItem
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param string $name
     * @return WpmlContentItem
     */
    public function setName(string $name): WpmlContentItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $wrapTag
     * @return WpmlContentItem
     */
    public function setWrapTag($wrapTag)
    {
        $this->wrapTag = $wrapTag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWrapTag()
    {
        return $this->wrapTag;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'isTranslatable' => $this->isTranslatable,
            'sourceData' => $this->sourceData,
            'translatedData' => $this->translatedData,
            'format' => $this->format,
            'wrapTag' => $this->wrapTag
        ];
    }

    /**
     * @return array
     */
    public function toLocJsonArray(): array
    {
        return [
            'id' => $this->key,
            'sourceText' => $this->sourceData,
            'format' => 'auto',
            'context' => $this->name,
            'properties' => [
                'wrap-tag' => $this->wrapTag,
                'format' => $this->format
            ],
            'existingTranslation' => ''
        ];
    }

    public function setSourceData($key, $data, $format, $useDecoder = false): WpmlContentItem
    {
        /** @var TranslationManagement $iclTranslationManagement */
        global $iclTranslationManagement;

        $this->sourceData = $useDecoder
            ? $iclTranslationManagement->decode_field_data($data, $format)
            : $data;

        if ($key === 'URL') {
            $this->sourceData = urldecode($this->sourceData);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSourceData(): string
    {
        return $this->sourceData;
    }

    /**
     * @return string|null
     */
    public function getTranslatedData(): string
    {
        return $this->translatedData;
    }

    /**
     * @param string|null $translatedData
     * @return WpmlContentItem
     */
    public function setTranslatedData(string $translatedData): WpmlContentItem
    {
        $this->translatedData = $translatedData;
        return $this;
    }
}
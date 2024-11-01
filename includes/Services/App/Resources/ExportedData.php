<?php

namespace Smartcat\Includes\Services\App\Resources;

class ExportedData
{
    /** @var string */
    private $title;

    /** @var string */
    private $slug;

    /** @var string */
    private $gutenbergContent;

    /** @var array */
    private $metadata;

    /** @var array */
    private $elementor;

    public function __construct($title, $slug, $gutenbergContent, $metadata, $elementor)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->gutenbergContent = $gutenbergContent;
        $this->metadata = $metadata;
        $this->elementor = $elementor;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getGutenbergContent()
    {
        return $this->gutenbergContent;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return array
     */
    public function getElementor()
    {
        return $this->elementor;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return urlencode($this->slug);
    }
}
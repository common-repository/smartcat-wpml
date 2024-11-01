<?php

namespace Smartcat\Includes\Services\Mocks\Models;

class SmartcatProject
{
    private string $id;

    private string $name;

    private string $sourceLocale;

    private array $targetLocales;

    private array $documents;

    /**
     * @param string $id
     * @return SmartcatProject
     */
    public function setId(string $id): SmartcatProject
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return SmartcatProject
     */
    public function setName(string $name): SmartcatProject
    {
        $this->name = $name;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $sourceLocale
     * @return SmartcatProject
     */
    public function setSourceLocale(string $sourceLocale): SmartcatProject
    {
        $this->sourceLocale = $sourceLocale;
        return $this;
    }

    /**
     * @param array $targetLocales
     * @return SmartcatProject
     */
    public function setTargetLocales(array $targetLocales): SmartcatProject
    {
        $this->targetLocales = $targetLocales;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    /**
     * @return array
     */
    public function getTargetLocales(): array
    {
        return $this->targetLocales;
    }

    /**
     * @param array $documents
     * @return SmartcatProject
     */
    public function setDocuments(array $documents): SmartcatProject
    {
        $this->documents = $documents;
        return $this;
    }

    /**
     * @return array
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}
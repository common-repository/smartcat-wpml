<?php

namespace Smartcat\Includes\Services\API\Models;

class Project
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string|null */
    private $deadline;

    private $documents;

    private $sourceLocale;

    /** @var ?string */
    private $externalTag;

    /**
     * @param string $id
     * @return Project
     */
    public function setId(string $id): Project
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return Project
     */
    public function setName(string $name): Project
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
     * @param string|null $deadline
     * @return Project
     */
    public function setDeadline($deadline): Project
    {
        $this->deadline = $deadline;
        return $this;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getDeadline()
    {
        if (!is_null($this->deadline)) {
            $deadline = new \DateTime($this->deadline);
            return $deadline->format('Y/m/d H:i:s');
        }

        return $this->deadline;
    }

    /**
     * @param mixed $documents
     * @return Project
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param mixed $sourceLocale
     * @return Project
     */
    public function setSourceLocale($sourceLocale)
    {
        $this->sourceLocale = $sourceLocale;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceLocale()
    {
        return $this->sourceLocale;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'deadline' => $this->deadline,
            'sourceLocale' => $this->sourceLocale
        ];
    }

    /**
     * @param string|null $externalTag
     * @return Project
     */
    public function setExternalTag($externalTag): Project
    {
        $this->externalTag = $externalTag;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalTag()
    {
        return $this->externalTag;
    }
}
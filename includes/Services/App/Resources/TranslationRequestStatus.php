<?php

namespace Smartcat\Includes\Services\App\Resources;

class TranslationRequestStatus
{
    /** @var string */
    private $type;

    /** @var float|int */
    private $progress;

    /**
     * @param string $type
     * @return TranslationRequestStatus
     */
    public function setType(string $type): TranslationRequestStatus
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param float|int $progress
     * @return TranslationRequestStatus
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
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
     * @return float|int
     */
    public function getProgress()
    {
        return $this->progress;
    }
}
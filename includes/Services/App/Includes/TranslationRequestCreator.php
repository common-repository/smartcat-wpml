<?php

namespace Smartcat\Includes\Services\App\Includes;

trait TranslationRequestCreator
{
    /** @var string */
    private $uuid;

    protected function createdAt(): string
    {
        return date('Y-m-d H:i:s');
    }

    protected function uuid(): string
    {
        return $this->uuid ?? $this->uuid = uniqid('tr_');
    }
}
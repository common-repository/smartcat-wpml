<?php

namespace Smartcat\Includes\Services\Interfaces;

interface PostTypeInterface
{
    public function getTranslatableTypes(): array;

    public function getWpmlPostTypes(): array;
}
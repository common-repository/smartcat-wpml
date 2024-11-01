<?php

namespace Smartcat\Includes\Services\Interfaces;

interface CustomFieldInterface
{
    public function getValue($key);

    public function isLocalizable($key): bool;
}
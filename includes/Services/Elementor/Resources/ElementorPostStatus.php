<?php

namespace Smartcat\Includes\Services\Elementor\Resources;

class ElementorPostStatus
{
    /** @var bool */
    private $isBuiltWithElementor = false;

    /** @var bool */
    private $hasErrors = false;

    /**
     * @return bool
     */
    public function isBuiltWithElementor(): bool
    {
        return $this->isBuiltWithElementor;
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /**
     * @param bool $isBuiltWithElementor
     * @return ElementorPostStatus
     */
    public function setIsBuiltWithElementor(bool $isBuiltWithElementor): ElementorPostStatus
    {
        $this->isBuiltWithElementor = $isBuiltWithElementor;
        return $this;
    }

    /**
     * @param bool $hasErrors
     * @return ElementorPostStatus
     */
    public function setHasErrors(bool $hasErrors): ElementorPostStatus
    {
        $this->hasErrors = $hasErrors;
        return $this;
    }
}
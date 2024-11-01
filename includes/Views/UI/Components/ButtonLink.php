<?php

namespace Smartcat\Includes\Views\UI\Components;

class ButtonLink extends Component
{
    private $label;

    public function render()
    {
        ?>
        <button
            class="sc sc-btn-link <?php echo $this->getCustomClassesString() ?>"
            id="<?php echo $this->getElementId() ?>"
        >
            <?php echo $this->label ?>
        </button>
        <?php
    }

    /**
     * @param mixed $label
     * @return ButtonLink
     */
    public function label($label): ButtonLink
    {
        $this->label = $label;
        return $this;
    }
}
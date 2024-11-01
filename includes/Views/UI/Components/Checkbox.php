<?php

namespace Smartcat\Includes\Views\UI\Components;

class Checkbox extends Component
{
    private $text = '';

    private $isChecked = false;

    public function render()
    {
        ?>
        <label
            data-tooltip="<?php echo $this->getTooltipData() ?>"
            class="sc-checkbox sc-control-checkbox <?php echo $this->hasTooltip() ? 'sc_tooltip' : '' ?> <?php echo $this->getCustomClassesString() ?>"
        >
            <span class="sc-checkbox__label"><?php echo $this->text ?></span>
            <input
                type="checkbox"
                id="<?php echo $this->getElementId() ?>"
                <?php echo $this->isChecked ? 'checked' : '' ?>
            />
            <span class="sc-checkbox__indicator"></span>
        </label>
        <?php
    }

    /**
     * @param string $text
     * @return Checkbox
     */
    public function text(string $text): Checkbox
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param bool $isChecked
     * @return Checkbox
     */
    public function isChecked(bool $isChecked): Checkbox
    {
        $this->isChecked = $isChecked;
        return $this;
    }
}
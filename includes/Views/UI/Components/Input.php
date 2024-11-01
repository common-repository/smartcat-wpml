<?php

namespace Smartcat\Includes\Views\UI\Components;

class Input extends Component
{
    private $type = 'text';
    private $value = '';
    private $placeholder = '';
    private $label = '';
    private $isRequired = false;
    private $hasError = false;
    private $errorMessage = '';
    private $isReadonly = false;

    public function render()
    {
        ?>
        <div class="sc-input-wrapper">
            <label class="sc-label" for="<?php echo $this->getElementId() ?>">
                <span><?php echo $this->label ?></span>
                <?php
                    if ($this->isRequired) {
                        ?>
                        <span class="sc-label-required">*</span>
                        <?php
                    }
                ?>
            </label>
            <input
                class="sc sc-input
                <?php echo $this->hasError ? 'error' : '' ?>
                <?php echo $this->getCustomClassesString() ?>"
                id="<?php echo $this->getElementId() ?>"
                style="<?php echo $this->getCssString() ?>"
                name="<?php echo $this->getElementName() ?>"
                type="<?php echo $this->type ?>"
                value="<?php echo $this->value ?>"
                placeholder="<?php echo $this->placeholder ?>"
                <?php echo $this->isDisabled() ? 'disabled' : ''?>
                <?php echo $this->isReadonly ? 'readonly' : ''?>
            >
            <span class="sc-input-error <?php echo !$this->hasError ? 'sc-dn' : '' ?>" for-input="<?php echo $this->getElementId() ?>">
                <?php $this->dangerIcon(); ?>
                <span>
                    <?php echo $this->errorMessage ?>
                </span>
            </span>
        </div>
        <?php
    }

    /**
     * @param string $type
     * @return Input
     */
    public function type(string $type): Input
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $value
     * @return Input
     */
    public function value(string $value): Input
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $placeholder
     * @return Input
     */
    public function placeholder(string $placeholder): Input
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param string $label
     * @return Input
     */
    public function label(string $label): Input
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return Input
     */
    public function required(): Input
    {
        $this->isRequired = true;
        return $this;
    }

    public function error($message = ''): Input
    {
        $this->hasError = true;
        $this->errorMessage = $message;
        return $this;
    }

    private function dangerIcon()
    {
        ?>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4.47636 1.59936C5.1535 0.426518 6.84635 0.426516 7.52349 1.59936L11.5916 8.64554C12.2687 9.81838 11.4223 11.2844 10.068 11.2844H1.93181C0.57753 11.2844 -0.268897 9.81838 0.408242 8.64554L4.47636 1.59936Z" fill="#FB3048"/>
            <path d="M6 4.39746V6.29814" stroke="#FFECEE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="5.99978" cy="8.79568" r="0.776144" fill="#FFECEE"/>
        </svg>
        <?php
    }

    /**
     * @return Input
     */
    public function readonly(): Input
    {
        $this->isReadonly = true;
        return $this;
    }
}
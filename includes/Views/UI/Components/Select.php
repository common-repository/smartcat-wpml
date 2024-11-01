<?php

namespace Smartcat\Includes\Views\UI\Components;

class Select extends Component
{
    private $placeholder = '';

    private $selectedItem = '';

    private $label = '';
    private $isRequired = false;
    private $hasError = false;
    private $errorMessage = '';
    private $options = [];

    public function render()
    {
        ?>
        <div class="sc-select-wrapper">
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
            <button
                id="<?php echo $this->getElementId() ?>"
                class="sc sc-select
                <?php echo $this->hasError ? 'error' : '' ?>
                <?php echo $this->getCustomClassesString() ?>"
                name="<?php echo $this->getElementName() ?>"
            >
                <span class="sc-select__placeholder">
                    <?php echo $this->placeholder ?>
                </span>
                <span class="sc-select__selected sc-dn">
                    Hello
                </span>
                <?php $this->downArrow() ?>
                <ul class="sc-select__list">
                    <?php
                        foreach ($this->options as $key => $value) {
                            ?>
                            <li class="sc-select__item" value="<?php echo $key ?>"><?php echo $value ?></li>
                            <?php
                        }
                    ?>
                </ul>
            </button>
            <span class="sc-input-error <?php echo !$this->hasError ? 'sc-dn' : '' ?>">
                <?php $this->dangerIcon(); ?>
                <span>
                    <?php echo $this->errorMessage ?>
                </span>
            </span>
        </div>
        <?php
    }

    /**
     * @param string $placeholder
     * @return Select
     */
    public function placeholder(string $placeholder): Select
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param string $selectedItem
     * @return Select
     */
    public function selectedItem(string $selectedItem): Select
    {
        $this->selectedItem = $selectedItem;
        return $this;
    }

    private function downArrow()
    {
        ?>
        <svg width="9" height="6" viewBox="0 0 9 6" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.929688 1.2168L4.49966 4.78683L8.06969 1.2168" stroke="#797389" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <?php
    }

    /**
     * @param string $label
     * @return Select
     */
    public function label(string $label): Select
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return Select
     */
    public function required(): Select
    {
        $this->isRequired = true;
        return $this;
    }

    public function error($message = ''): Select
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
     * @param array $options
     * @return Select
     */
    public function options(array $options): Select
    {
        $this->options = $options;
        return $this;
    }
}
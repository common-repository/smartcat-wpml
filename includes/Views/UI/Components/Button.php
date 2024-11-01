<?php

namespace Smartcat\Includes\Views\UI\Components;

class Button extends Component
{
    const SIMPLE = 'simple';
    const DANGER = 'danger';

    const FLAT = 'flat';

    const SMALL_SIZE = 'small';

    private $label = NULL;
    private $type = NULL;
    private $size = 'default';
    private $icon;
    private $onRight = false;
    private $loader = false;
    private $onlyIcon = false;
    private $tooltip;
    private $link = null;

    public function render()
    {
        ?>
        <div class="sc-button-wrapper">
            <<?php echo $this->link ? "a href='$this->link' target='_blank'" : 'button'?>
                class="sc sc-button
                <?php echo "$this->size-size"?>
                <?php echo $this->loader ? 'loader' : '' ?>
                <?php echo $this->onRight ? 'on-right' : '' ?>
                <?php echo $this->onlyIcon ? 'only-icon' : '' ?>
                <?php echo $this->type ?> <?php echo $this->getCustomClassesString() ?>"
                id="<?php echo $this->getElementId() ?>"
                <?php echo $this->isDisabled() ? 'disabled' : '' ?>
            >
                <p class="sc-button__content"><?php echo $this->label ?></p>
                <?php
                sc_ui()
                    ->loader()
                    ->isShow($this->loader)
                    ->render();

                if (isset($this->icon) && !$this->loader) {
                    ?>
                    <span class="sc-button__icon dashicons dashicons-<?php echo $this->icon ?>"></span>
                    <?php
                }
                ?>
                <?php
                if (isset($this->tooltip)) {
                    ?>
                    <p class="sc-tooltip"><?php echo $this->tooltip ?></p>
                    <?php
                }
                ?>
            </<?php echo $this->link ? 'a' : 'button'?>>
        </div>
        <?php
    }

    /**
     * @param null $label
     * @return Button
     */
    public function label($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param null $type
     * @return Button
     */
    public function style($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $icon
     * @return Button
     */
    public function icon(string $icon): Button
    {
        $this->icon = $icon;
        return $this;
    }

    public function onRight(): Button
    {
        $this->onRight = true;
        return $this;
    }

    /**
     * @param bool $loader
     * @return Button
     */
    public function loader(bool $loader): Button
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @return Button
     */
    public function onlyIcon(): Button
    {
        $this->onlyIcon = true;
        return $this;
    }

    /**
     * @param null $size
     * @return Button
     */
    public function size($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param mixed $tooltip
     * @return Button
     */
    public function tooltip($tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * @param null $link
     * @return Button
     */
    public function link($link)
    {
        $this->link = $link;
        return $this;
    }
}
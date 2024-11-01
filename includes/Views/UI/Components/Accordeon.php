<?php

namespace Smartcat\Includes\Views\UI\Components;

class Accordeon extends Component
{
    protected $label;

    /** @var \Closure|null */
    private $body = NULL;

    private $link = null;

    public function render()
    {
        ?>
        <<?php echo $this->link ? "a href='$this->link' target='_blank'" : 'div' ?> class="sc-accordion">
            <div class="sc-accordion__head">
                <span style="display: flex; align-items: center; column-gap: 10px">
                    <?php if ($this->link): ?>
                        <span class="dashicons dashicons-admin-links accordion__head--arrow"></span>
                    <?php endif; ?>
                    <?php sc_ui()->text()->content($this->label)->render(); ?>
                </span>
                <?php if (!$this->link): ?>
                    <span class="dashicons dashicons-arrow-down-alt2 accordion__head--arrow"></span>
                <?php endif; ?>
            </div>
            <?php if (!$this->link): ?>
                <div class="sc-accordion__body">
                    <?php $this->renderBody(); ?>
                </div>
            <?php endif; ?>
        </<?php echo $this->link ? 'a' : 'div' ?>>
        <?php
    }

    /**
     * @param mixed $label
     * @return Accordeon
     */
    public function label($label)
    {
        $this->label = $label;
        return $this;
    }

    public function body($function): Accordeon
    {
        $this->body = $function;
        return $this;
    }

    private function renderBody()
    {
        if ($this->body instanceof \Closure) {
            /** @var callable $callback */
            $callback = $this->body;
            $callback();
        }
    }

    /**
     * @param null $link
     * @return Accordeon
     */
    public function link($link)
    {
        $this->link = $link;
        return $this;
    }
}
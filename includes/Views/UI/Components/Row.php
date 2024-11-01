<?php

namespace Smartcat\Includes\Views\UI\Components;

class Row extends Component
{
    /** @var \Closure|null */
    private $body = NULL;

    /** @var string[] */
    private $classes = [];

    public function render()
    {
        ?>
        <div style="<?php echo $this->getCssString() ?>" class="sc sc-row <?php echo $this->getClasses() ?>" id="<?php echo $this->getElementId() ?>">
            <?php
            $this->renderBody();
            ?>
        </div>
        <?php
    }

    public function body($function): Row
    {
        $this->body = $function;
        return $this;
    }

    public function flex(): Row
    {
        $this->classes[] = 'sc-df';
        return $this;
    }

    public function alignItemsCenter(): Row
    {
        $this->classes[] = 'sc-aic';
        return $this;
    }

    public function justifyContentBetween(): Row
    {
        $this->classes[] = 'sc-jcsb';
        return $this;
    }

    public function flexDirectionColumn(): Row
    {
        $this->classes[] = 'sc-fdc';
        return $this;
    }

    public function justifyContentFlexEnd(): Row
    {
        $this->classes[] = 'sc-jcfe';
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

    private function getClasses(): string
    {
        return implode(' ', array_merge(
            $this->classes,
            $this->getCustomClasses()
        ));
    }
}
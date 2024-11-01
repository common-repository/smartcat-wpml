<?php

namespace Smartcat\Includes\Views\UI\Components;

class Loader extends Component
{
    private $isShow = false;
    private $isColored = false;

    public function render()
    {
        ?>
        <div class="sc-loader <?php echo $this->getCustomClassesString() ?> <?php echo $this->isColored ? 'color' : ''?> <?php echo !$this->isShow ? 'sc-dn' : ''?>">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
        <?php
    }

    /**
     * @param mixed $isShow
     * @return Loader
     */
    public function isShow($isShow)
    {
        $this->isShow = $isShow;
        return $this;
    }

    public function isColored(): Loader
    {
        $this->isColored = true;
        return $this;
    }
}
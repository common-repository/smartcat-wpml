<?php

namespace Smartcat\Includes\Views\UI\Components;

class Text extends Component
{
    protected $content;
    protected $isBold = false;

    public function render()
    {
        ?>
        <span
            class="sc sc-text <?php echo $this->getCustomClassesString() ?>"
            style="<?php echo $this->isBold ? 'font-weight: bold;' : '' ?><?php echo $this->getCssString() ?>"
        ><?php echo $this->content ?></span>
        <?php
    }

    /**
     * @param mixed $content
     * @return Text
     */
    public function content($content): Text
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param mixed $isBold
     * @return Text
     */
    public function bold()
    {
        $this->isBold = true;
        return $this;
    }
}
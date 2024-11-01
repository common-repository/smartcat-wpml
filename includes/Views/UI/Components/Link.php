<?php

namespace Smartcat\Includes\Views\UI\Components;

class Link extends Component
{
    private $href = '#';

    private $text = '';

    private $targetBlank = false;

    public function render()
    {
        ?>
        <a
            href="<?php echo $this->href ?>"
            <?php echo $this->targetBlank ? 'target="_blank"' : '' ?>
            class="sc sc-link <?php echo $this->getCustomClassesString() ?>"
        ><?php echo $this->text ?></a>
        <?php
    }

    /**
     * @param string $text
     * @return Link
     */
    public function text(string $text): Link
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param string $href
     * @return Link
     */
    public function href(string $href): Link
    {
        $this->href = $href;
        return $this;
    }

    /**
     * @return Link
     */
    public function targetBlank(): Link
    {
        $this->targetBlank = true;
        return $this;
    }
}
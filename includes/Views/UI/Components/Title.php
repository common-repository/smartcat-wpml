<?php

namespace Smartcat\Includes\Views\UI\Components;

class Title extends Component
{
    const H1 = 'h1';
    const H2 = 'h2';
    const H3 = 'h3';
    const H4 = 'h4';
    const H5 = 'h5';
    const H6 = 'h6';

    private $text = '';
    private $level = 'h1';

    public function render()
    {
        ?>
        <<?php echo $this->level ?> class="sc-title <?php echo $this->getCustomClassesString() ?>">
            <?php echo $this->text ?>
        </<?php echo $this->level ?>>
        <?php
    }

    /**
     * @param string $text
     * @return Title
     */
    public function text(string $text): Title
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param string $level
     * @return Title
     */
    public function level(string $level): Title
    {
        $this->level = $level;
        return $this;
    }
}
<?php

namespace Smartcat\Includes\Views\UI;

use Smartcat\Includes\Views\UI\Components\Accordeon;
use Smartcat\Includes\Views\UI\Components\Button;
use Smartcat\Includes\Views\UI\Components\ButtonLink;
use Smartcat\Includes\Views\UI\Components\Checkbox;
use Smartcat\Includes\Views\UI\Components\ConfirmPopup;
use Smartcat\Includes\Views\UI\Components\Form;
use Smartcat\Includes\Views\UI\Components\Input;
use Smartcat\Includes\Views\UI\Components\Link;
use Smartcat\Includes\Views\UI\Components\Loader;
use Smartcat\Includes\Views\UI\Components\Logo;
use Smartcat\Includes\Views\UI\Components\Notice;
use Smartcat\Includes\Views\UI\Components\Popup;
use Smartcat\Includes\Views\UI\Components\Row;
use Smartcat\Includes\Views\UI\Components\Select;
use Smartcat\Includes\Views\UI\Components\Text;
use Smartcat\Includes\Views\UI\Components\Title;

class UI
{
    public function button(): Button
    {
        return new Button();
    }

    public function loader(): Loader
    {
        return new Loader();
    }

    public function logo(): Logo
    {
        return new Logo();
    }

    public function title(): Title
    {
        return new Title();
    }

    public function row(): Row
    {
        return new Row();
    }

    public function text(): Text
    {
        return new Text();
    }

    public function link(): Link
    {
        return new Link();
    }

    public function blink(): ButtonLink
    {
        return new ButtonLink();
    }

    public function popup(): Popup
    {
        return new Popup();
    }

    public function input(): Input
    {
        return new Input();
    }

    public function select(): Select
    {
        return new Select();
    }

    public function confirmPopup(): ConfirmPopup
    {
        return new ConfirmPopup();
    }

    public function notice(): Notice
    {
        return new Notice();
    }

    public function form(): Form
    {
        return new Form();
    }

    public function checkbox(): Checkbox
    {
        return new Checkbox();
    }

    public function accordion(): Accordeon
    {
        return new Accordeon();
    }

    public function hr()
    {
        echo '<hr class="sc-hr">';
    }
}
<?php

namespace Smartcat\Includes\Views\UI\Components;

class Form extends Component
{
    /** @var \Closure|null */
    private $body = NULL;

    private $action = NULL;

    public function render()
    {
        ?>
        <form action="<?php echo $this->action ?>" method="post" id="<?php echo $this->getElementId() ?>">
            <?php $this->renderBody() ?>
        </form>
        <?php
    }

    public function body($function): Form
    {
        $this->body = $function;
        return $this;
    }

    /**
     * @param null $action
     * @return Form
     */
    public function action($action): Form
    {
        $this->action = $action;
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
}
<?php

namespace Smartcat\Includes\Views\UI\Components;

class Popup extends Component
{
    private $actionButtonLabel = '';
    private $actionButtonId = '';

    private $title = '';

    private $width = 'auto';

    private $headLink = false;

    private $headLinkUrl = '#';

    /** @var \Closure|null */
    private $body = NULL;

    public function render()
    {
        ?>
        <div style="display: none" class="sc sc-popup <?php echo $this->getCustomClassesString() ?>" id="<?php echo $this->getElementId() ?>">
            <div class="sc-popup__wrapper" style="width: <?php echo $this->width ?>;">
                <div class="sc-popup__head">
                    <?php
                        sc_ui()
                            ->button()
                            ->style(Button::FLAT)
                            ->onlyIcon()
                            ->classes('close-icon')
                            ->icon('no-alt')
                            ->render()
                    ?>
                </div>
                <?php
                    sc_ui()
                        ->row()
                        ->classes('sc-popup__title-row')
                        ->flex()
                        ->alignItemsCenter()
                        ->justifyContentBetween()
                        ->body(function () {
                            sc_ui()
                                ->title()
                                ->classes('sc-popup__title')
                                ->text($this->title)
                                ->render();

                            if ($this->headLink) {
                                echo '<div>';
                                echo '<span style="color: #ABA7B3;" class="dashicons dashicons-editor-help"></span>';
                                sc_ui()
                                    ->link()
                                    ->classes('sc_help-link')
                                    ->targetBlank()
                                    ->href($this->headLinkUrl)
                                    ->text($this->headLink)
                                    ->render();
                                echo '</div>';
                            }
                        })
                        ->render();
                ?>
                <div class="sc-popup__body">
                    <?php $this->renderBody() ?>
                </div>
                <div class="sc-popup__footer">
                    <?php
                        sc_ui()
                            ->button()
                            ->classes('close')
                            ->label('Cancel')
                            ->style(Button::SIMPLE)
                            ->render();

                        sc_ui()
                            ->button()
                            ->tooltip('Please fill out all required fields')
                            ->id($this->actionButtonId)
                            ->label($this->actionButtonLabel)
                            ->render()
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * @param string $actionButtonLabel
     * @return Popup
     */
    public function actionButtonLabel(string $actionButtonLabel): Popup
    {
        $this->actionButtonLabel = $actionButtonLabel;
        return $this;
    }

    /**
     * @param string $title
     * @return Popup
     */
    public function title(string $title): Popup
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $width
     * @return Popup
     */
    public function width(string $width): Popup
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param $headLink
     * @param $url
     * @return Popup
     */
    public function headLink($headLink, $url): Popup
    {
        $this->headLink = $headLink;
        $this->headLinkUrl = $url;
        return $this;
    }

    public function body($function): Popup
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
     * @param string $actionButtonId
     * @return Popup
     */
    public function actionButtonId(string $actionButtonId): Popup
    {
        $this->actionButtonId = $actionButtonId;
        return $this;
    }
}
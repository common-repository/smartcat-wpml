<?php

namespace Smartcat\Includes\Views\UI\Components;

class ConfirmPopup extends Component
{
    private $actionButtonLabel = '';
    private $actionButtonId = '';
    private $actionButtonType = '';

    private $cancelButtonLabel = 'Cancel';
    private $cancelButtonId = '';

    private $title = '';

    private $description = '';

    private $width = '500px';

    public function render()
    {
        ?>
        <div style="display: none" class="sc sc-confirm-popup <?php echo $this->getCustomClassesString() ?>" id="<?php echo $this->getElementId() ?>">
            <div class="sc-confirm-popup__wrapper" style="width: <?php echo $this->width ?>;">
                <div class="sc-confirm-popup__head">
                    <?php
                        sc_ui()
                            ->button()
                            ->style(Button::FLAT)
                            ->onlyIcon()
                            ->classes('close')
                            ->icon('no-alt')
                            ->render()
                    ?>
                </div>
                <?php
                sc_ui()
                    ->title()
                    ->classes('sc-confirm-popup__title')
                    ->text($this->title)
                    ->render();
                ?>
                <div class="sc-confirm-popup__body">
                    <span>
                        <?php echo $this->description ?>
                    </span>
                </div>
                <div class="sc-confirm-popup__footer">
                    <?php
                        sc_ui()
                            ->button()
                            ->id($this->cancelButtonId)
                            ->classes('cancel')
                            ->size(Button::SMALL_SIZE)
                            ->label($this->cancelButtonLabel)
                            ->style(Button::SIMPLE)
                            ->render();

                        sc_ui()
                            ->button()
                            ->style($this->actionButtonType)
                            ->id($this->actionButtonId)
                            ->size(Button::SMALL_SIZE)
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
     * @return ConfirmPopup
     */
    public function actionButtonLabel(string $actionButtonLabel): ConfirmPopup
    {
        $this->actionButtonLabel = $actionButtonLabel;
        return $this;
    }

    /**
     * @param string $title
     * @return ConfirmPopup
     */
    public function title(string $title): ConfirmPopup
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $width
     * @return ConfirmPopup
     */
    public function width(string $width): ConfirmPopup
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $cancelButtonLabel
     * @return ConfirmPopup
     */
    public function cancelButtonLabel(string $cancelButtonLabel): ConfirmPopup
    {
        $this->cancelButtonLabel = $cancelButtonLabel;
        return $this;
    }

    /**
     * @param string $description
     * @return ConfirmPopup
     */
    public function description(string $description): ConfirmPopup
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $actionButtonId
     * @return ConfirmPopup
     */
    public function actionButtonId(string $actionButtonId): ConfirmPopup
    {
        $this->actionButtonId = $actionButtonId;
        return $this;
    }

    /**
     * @param string $cancelButtonId
     * @return ConfirmPopup
     */
    public function cancelButtonId(string $cancelButtonId): ConfirmPopup
    {
        $this->cancelButtonId = $cancelButtonId;
        return $this;
    }

    /**
     * @param string $actionButtonType
     * @return ConfirmPopup
     */
    public function actionButtonType(string $actionButtonType): ConfirmPopup
    {
        $this->actionButtonType = $actionButtonType;
        return $this;
    }
}
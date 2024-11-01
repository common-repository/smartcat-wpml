<?php

namespace Smartcat\Includes\Views\UI\Components;

class Notice extends Component
{
    public function render()
    {
        ?>
        <div class="sc-notification success" style="display: none">
            <div class="sc-notification__icon">
                <svg width="12" class="danger" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.4766 1.5974C5.15374 0.424565 6.84659 0.424562 7.52373 1.5974L11.5918 8.64358C12.269 9.81642 11.4226 11.2825 10.0683 11.2825H1.93205C0.577774 11.2825 -0.268653 9.81643 0.408486 8.64359L4.4766 1.5974Z" fill="#FFECEE"/>
                    <path d="M6 4.39551V6.29619" stroke="#FB3048" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="6.00002" cy="8.79372" r="0.776144" fill="#FB3048"/>
                </svg>
                <svg width="12" height="12" viewBox="0 0 12 12" class="success" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0.169922" y="0.169922" width="11.66" height="11.66" rx="3.89" fill="#E6FCF8"/>
                    <path d="M3.82666 6.41733L5.50998 8.1006L8.90359 4.70703" stroke="#00CBA8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="sc-notification__content"></p>
            <a href="#" class="sc-notification__close">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.42993 1.42979L8.56993 8.56969M1.42993 8.56958L8.56993 1.42969" stroke="#F2F1F4" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
            </a>
        </div>
        <?php
    }
}
<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Views\UI\Components\Button;

class FAQ
{
    /** @var Components $components */
    private $components;

    public function __construct()
    {
        $this->components = new Components();
        smartcat_hub_client()->sendMixpanelEvent('WPAppFaqOpened', true, [
            'refer' => $_GET['refer'] ?? 'unknown'
        ]);
    }

    public function display()
    {
        $this->components->startWrapper('98%', 'faq');
        sc_ui()->row()
            ->classes('sc-faq-header')
            ->flex()
            ->alignItemsCenter()
            ->justifyContentBetween()
            ->body(function () {
                sc_ui()
                    ->title()
                    ->text('Smartcat Helper')
                    ->render();

                sc_ui()
                    ->button()
                    ->link('https://help.smartcat.com/contact-us/')
                    ->label('Message to support')
                    ->icon('businessperson')
                    ->onRight()
                    ->size(Button::SMALL_SIZE)
                    ->render();
            })->render();

        echo '<hr style="margin-top: 20px;">';

        sc_ui()->row()
            ->classes()
            ->css(['margin-top' => '40px'])
            ->flex()
            ->alignItemsCenter()
            ->justifyContentBetween()
            ->body(function () {
                sc_ui()
                    ->title()
                    ->level('h2')
                    ->text('Frequently Asked Questions:')
                    ->render();
            })->render();

        sc_ui()
            ->row()
            ->classes('sc-faq-questions')
            ->body(function () {
                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/Not-all-content-is-sent-to-Smartcat-2490efd15c014f488ce675784c0b15ad?pvs=4')
                    ->label('Not all content is sent to Smartcat')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/I-m-having-trouble-getting-translations-from-Smartcat-8e208320c11c42e29b7abcd7eb7fd0cf?pvs=4')
                    ->label('I\'m having trouble getting translations from Smartcat')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/I-have-problems-with-a-translation-request-94b03119bb1d43459f03b61b5039d66d?pvs=4')
                    ->label('I have problems with a translation request')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/How-to-translate-WPML-strings-0cf2ac3192c24341922f96890cd723be?pvs=4')
                    ->label('How to translate WPML strings?')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/What-if-I-have-to-contact-to-WPML-support-7592ad4a7a3f40bf950e48beca985626?pvs=4')
                    ->label('What if I have to contact to WPML support?')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/How-to-provide-supporters-with-a-copy-of-your-site-56276ecae9804324813cb4f2793cd4dc?pvs=4')
                    ->label('How to provide supporters with a copy of your site')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/I-have-unnecessary-content-being-sent-to-Smartcat-4c67bac6f6c648c996076029ce5a925c?pvs=4')
                    ->label('I have unnecessary content being sent to Smartcat')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/I-have-problems-with-automatic-import-of-translation-bed701d4dd554c06905416b12d3db1f1?pvs=4')
                    ->label('I have problems with automatic import of translation')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/Disable-WPML-strings-registration-for-a-post-when-sending-content-to-Smartcat-9836bc9a6ed340f0b076101814f57c6d?pvs=4')
                    ->label('Disable WPML strings registration for a post when sending content to Smartcat')
                    ->render();

                sc_ui()
                    ->accordion()
                    ->link('https://smartcat1.notion.site/Maximum-number-of-translated-items-to-pull-from-Smartcat-per-request-644342758f304242af6e5391df824a97?pvs=4s')
                    ->label('Maximum number of translated items to pull from Smartcat per request')
                    ->render();
            })->render();

        sc_ui()
            ->row()
            ->css(['margin-top' => '20px'])
            ->flex()
            ->justifyContentFlexEnd()
            ->body(function () {
                sc_ui()
                    ->link()
                    ->text('Go to Smartcat Helper in Notion')
                    ->targetBlank()
                    ->href('https://smartcat1.notion.site/WordPress-App-Helper-External-f913a58c0f3c448e95563d93ff9e0c30?pvs=4')
                    ->render();
            })->render();

        $this->components->endWrapper();
    }

    private function getImage($path)
    {
        echo "<a href=\"$path\" target='_blank'><img class=\"sc-accordion-image\" src=\"$path\"></a>";
    }
}
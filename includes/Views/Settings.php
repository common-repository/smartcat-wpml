<?php

namespace Smartcat\Includes\Views;

use Smartcat\Includes\Services\API\SmartcatClient;
use Smartcat\Includes\Views\UI\Components\Button;
use Smartcat\Includes\Views\UI\Components\Icon;
use Smartcat\Includes\Services\Plugin\Options;
use Smartcat\Includes\Services\Plugin\Migrations;

class Settings
{
    private $screen = 'app';
    private $workspaceInfo = [
        'workspace' => 'Error',
        'organization' => 'Error',
    ];

    public function __construct()
    {
        $this->detectScreen();
        $workspaceInfo = smartcat_hub_client()->workspaceInfo(
            get_option('smartcat_account_id')
        );

        if (!is_wp_error($workspaceInfo)) {
            $this->workspaceInfo['workspace'] = $workspaceInfo['workspaceName'] ?? 'error';
            $this->workspaceInfo['organization'] = $workspaceInfo['organizationName'] ?? 'error';
        }
    }

    public function display()
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'upgrade' && !SC_LOCAL_ENV) {
             wp_update_plugin('smartcat-wpml/smartcat-wpml.php');
        }

        $this->head();

        $this->appScreen();
        $this->classicIntegrationScreen();

        $this->appCredentialsPopup();
        $this->authUnexpectedErrorPopup();
        $this->regenerateSecretPopup();
        $this->disconnectConfirmPopup();
        sc_ui()->notice()->render();

        $this->debug();
        $this->migrations();
    }

    private function appScreen()
    {
        sc_ui()->row()
            ->id('sc-app-screen')
            ->classes('sc-settings__app-screen')
            ->css($this->maybeDisplayNone('app'))
            ->body(function () {

                sc_ui()->row()
                    ->classes('sc-settings__app-screen--description')
                    ->body(function () {
                        sc_ui()->text()
                            ->content('Send translation requests right from WordPress. Connect to Smartcat to continue.')
                            ->render();

                        sc_ui()->link()
                            ->href('https://help.smartcat.com/integrations/wordpress-app')
                            ->targetBlank()
                            ->text('See how to use Smartcat integration')
                            ->render();
                        sc_ui()->text()
                            ->classes('sc_c-point')
                            ->content('.')
                            ->render();
                    })->render();

                $organizationName = $this->workspaceInfo['organization'];
                $workspaceName = $this->workspaceInfo['workspace'];
                $accountName = $this->workspaceInfo['organization'];
                $accountName .= $organizationName !== $workspaceName
                    ? " / $workspaceName"
                    : '';

                $user = sc_ui()->row()
                    ->flex()
                    ->id('sc-user')
                    ->classes('sc-account-wrapper')
                    ->alignItemsCenter()
                    ->body(function () use ($accountName) {
                        sc_ui()
                            ->text()
                            ->classes('sc-account-name')
                            ->content("Connected to <span id='sc-account-name'>[$accountName]</span>")
                            ->render();

                        sc_ui()
                            ->form()
                            ->id('sc-disconnect-form')
                            ->action(esc_url(admin_url('admin-post.php')))
                            ->body(function () {
                                sc_ui()
                                    ->input()
                                    ->type('hidden')
                                    ->name('action')
                                    ->value('smartcat_logout')
                                    ->render();

                                sc_ui()
                                    ->input()
                                    ->type('hidden')
                                    ->name('smartcat_update_options_nonce')
                                    ->value(wp_create_nonce('smartcat_update_options_form_nonce'))
                                    ->render();

                                sc_ui()
                                    ->button()
                                    ->id('sc-disconnect-btn')
                                    ->label('Disconnect')
                                    ->size(Button::SMALL_SIZE)
                                    ->style(Button::FLAT)
                                    ->render();
                            })
                            ->render();
                    });

                $login = sc_ui()->row()
                    ->classes('sc-settings__app-screen--buttons')
                    ->id('sc-login')
                    ->flex()
                    ->alignItemsCenter()
                    ->body(function () {
                        sc_ui()
                            ->form()
                            ->action(esc_url(admin_url('admin-post.php')))
                            ->body(function () {
                                sc_ui()
                                    ->input()
                                    ->type('hidden')
                                    ->name('action')
                                    ->value('smartcat_log_in')
                                    ->render();

                                sc_ui()
                                    ->input()
                                    ->type('hidden')
                                    ->name('smartcat_update_options_nonce')
                                    ->value(wp_create_nonce('smartcat_update_options_form_nonce'))
                                    ->render();

                                sc_ui()
                                    ->button()
                                    ->id('sc-connect-to-smartcat')
                                    ->label('Connect to Smartcat')
                                    ->icon(Icon::EXTERNAL)
                                    ->render();

                            })
                            ->render();

                        sc_ui()->button()
                            ->id('sc-authenticate-manually')
                            ->style(Button::SIMPLE)
                            ->label('Authenticate manually')
                            ->render();

                    });


                if (SmartcatClient::isAuthorized()) {
                    $login->css(['display' => 'none']);
                } else {
                    $user->css(['display' => 'none']);
                }

                $user->render();
                $login->render();

                sc_ui()->blink()
                    ->id('sc-to-classic-integration')
                    ->label('I need a secret key to set up integrations from Smartcat')
                    ->render();

                echo '<hr style="margin-top: 40px;">';

                sc_ui()
                    ->title()
                    ->classes('sc-settings-section-title')
                    ->text('Settings')
                    ->level('h3')
                    ->render();

                sc_ui()
                    ->checkbox()
                    ->isChecked(sc_check_option('smartcat_always_wp_editor'))
                    ->id('sc-use-always-wp-editor')
                    ->tooltipText('This will avoid problems when using the editor from WPML')
                    ->text('Use always classic WordPress editor')
                    ->render();

                sc_ui()
                    ->checkbox()
                    ->isChecked(sc_check_option('sc_automatically_get_translations'))
                    ->classes('sc-automatically-get-translations-checkbox')
                    ->id('sc-automatically-get-translations')
                    ->tooltipText('Translations will be automatically imported into WordPress every 15 minutes.')
                    ->text('Automatically receive translations from Smartcat')
                    ->render();


                sc_ui()
                    ->row()
                    ->flex()
                    ->alignItemsCenter()
                    ->css(['gap' => '5px', 'margin-top' => '8px'])
                    ->body(function () {
                        sc_ui()
                            ->checkbox()
                            ->isChecked(sc_check_option('sc_disable_wpml_strings_register'))
                            ->id('sc-disable-wpml-strings-register')
                            ->text('Disable WPML strings registration for a post when sending content to Smartcat')
                            ->render();

                        sc_ui()
                            ->button()
                            ->onlyIcon()
                            ->link('https://smartcat1.notion.site/Disable-WPML-strings-registration-for-a-post-when-sending-content-to-Smartcat-9836bc9a6ed340f0b076101814f57c6d?pvs=4')
                            ->style(Button::FLAT)
                            ->size(Button::SMALL_SIZE)
                            ->icon('editor-help')
                            ->render();
                    })->render();

                echo '<hr style="margin-top: 20px;margin-bottom: 30px">';

                sc_ui()
                    ->row()
                    ->flex()
                    ->flexDirectionColumn()
                    ->css(['gap' => '5px', 'margin-top' => '8px'])
                    ->body(function () {
                        sc_ui()
                            ->text()
                            ->bold()
                            ->content('Maximum number of translated items to pull from Smartcat per request:')
                            ->render();

                        sc_ui()
                            ->row()
                            ->flex()
                            ->alignItemsCenter()
                            ->css(['gap' => '5px'])
                            ->body(function () {
                                sc_ui()
                                    ->input()
                                    ->type('number')
                                    ->id('sc-number-of-items-to-receive-translations')
                                    ->css(['width' => '100px'])
                                    ->value(get_option('sc_number_of_items_to_receive_translations', 5))
                                    ->render();

                                sc_ui()
                                    ->button()
                                    ->onlyIcon()
                                    ->link('https://smartcat1.notion.site/Maximum-number-of-translated-items-to-pull-from-Smartcat-per-request-644342758f304242af6e5391df824a97?pvs=4s')
                                    ->style(Button::FLAT)
                                    ->size(Button::SMALL_SIZE)
                                    ->icon('editor-help')
                                    ->render();
                            })->render();
                    })->render();

                echo '<hr style="margin-top: 30px;">';

                sc_ui()
                    ->row()
                    ->body(function () {
                        sc_ui()
                            ->button()
                            ->id('sc-save-settings-button')
                            ->classes('sc-save-settings-button')
                            ->label('Save changes')
                            ->render();
                    })
                    ->render();
            })->render();
    }

    private function classicIntegrationScreen()
    {
        sc_ui()
            ->row()
            ->id('sc-integration-screen')
            ->css($this->maybeDisplayNone('integration'))
            ->classes('sc-settings__integration-screen')
            ->body(function () {
                sc_ui()
                    ->row()
                    ->classes('sc-settings__integration-screen--description')
                    ->body(function () {
                        sc_ui()->text()
                            ->content('Use this secret key when setting up a new WPML integration project in Smartcat.')
                            ->render();

                        sc_ui()->link()
                            ->href('https://help.smartcat.com/integrations/wordpress-wpml-integration/')
                            ->targetBlank()
                            ->text('See how to use Smartcat Integration for WPML')
                            ->render();

                        sc_ui()->text()
                            ->classes('sc_c-point')
                            ->content('.')
                            ->render();
                    })
                    ->render();

                sc_ui()
                    ->row()
                    ->classes('sc-settings__integration-screen--key')
                    ->flex()
                    ->alignItemsCenter()
                    ->body(function () {
                        sc_ui()
                            ->input()
                            ->id('sc-masked-secret')
                            ->value(Options::maskSecret())
                            ->readonly()
                            ->render();

                        sc_ui()
                            ->input()
                            ->id('sc-not-masked-secret')
                            ->value(Options::secret())
                            ->type('hidden')
                            ->render();

                        sc_ui()
                            ->button()
                            ->id('sc-copy-secret')
                            ->label('Copy key')
                            ->render();

                        sc_ui()
                            ->button()
                            ->onlyIcon()
                            ->id('sc-generate-new-key')
                            ->icon('update-alt')
                            ->style(Button::FLAT)
                            ->render();

                    })->render();

                sc_ui()
                    ->blink()
                    ->id('sc-to-app-screen')
                    ->label('Connect to Smartcat account to create translation requests from WordPress')
                    ->render();

            })->render();
    }

    private function head()
    {
        sc_ui()->row()
            ->flex()
            ->alignItemsCenter()
            ->classes('sc-settings__head')
            ->body(function () {
                sc_ui()
                    ->logo()
                    ->render();
                sc_ui()
                    ->title()
                    ->text('Smartcat Integration for WPML')
                    ->render();
            })->render();
    }

    private function authUnexpectedErrorPopup()
    {
        sc_ui()
            ->confirmPopup()
            ->title('Unexpected error')
            ->cancelButtonLabel('Back to settings')
            ->actionButtonLabel('Check credentials')
            ->description('We couldn’t create the integration. Please check the credentials and try again or contact support so we can help you out.')
            ->render();
    }

    private function appCredentialsPopup()
    {
        sc_ui()
            ->popup()
            ->id('sc-account-params-popup')
            ->classes('sc-account-params')
            ->title('Authenticate manually')
            ->actionButtonLabel('Authenticate')
            ->actionButtonId('sc-register-credentials-action')
            ->headLink('How does it work?', 'https://help.smartcat.com/integrations/wordpress-app')
            ->width('600px')
            ->body(function () {

                sc_ui()
                    ->select()
                    ->id('sc-server')
                    ->label('Server')
                    ->name('server')
                    ->placeholder('Select server')
                    ->options([
                        'ea' => 'Asia',
                        'eu' => 'Europe',
                        'us' => 'USA'
                    ])
                    ->required()
                    ->render();

                sc_ui()
                    ->input()
                    ->label('Account ID')
                    ->id('sc-account-id')
                    ->name('accountId')
                    ->placeholder('Enter your account ID')
                    ->required()
                    ->render();

                sc_ui()
                    ->input()
                    ->label('API key')
                    ->id('sc-api-key')
                    ->name('apiKey')
                    ->placeholder('Enter a API key')
                    ->required()
                    ->render();

            })
            ->render();
    }

    private function regenerateSecretPopup()
    {
        sc_ui()
            ->confirmPopup()
            ->id('sc-generate-new-key-popup')
            ->width('484px')
            ->title('Generate a new key?')
            ->cancelButtonLabel('No, cancel')
            ->description('If you generate a new key, the integrations using the old key will not work anymore')
            ->actionButtonLabel('Yes, generate new key')
            ->actionButtonId('sc-generate-new-key-popup-action')
            ->actionButtonType(Button::DANGER)
            ->render();
    }

    private function detectScreen()
    {
        $screen = $_GET['screen'] ?? NULL;

        if (in_array($screen, ['app', 'integration'])) {
            $this->screen = $screen;
        }
    }

    private function maybeDisplayNone($screen): array
    {
        return $screen !== $this->screen
            ? ['display' => 'none']
            : [];
    }

    private function disconnectConfirmPopup()
    {
        sc_ui()
            ->confirmPopup()
            ->id('sc-disconnect-confirm-popup')
            ->title('Disconnect account?')
            ->width('484px')
            ->description('You won’t be able to work with your translation requests')
            ->cancelButtonLabel('No, cancel')
            ->actionButtonLabel('Yes, disconnect')
            ->actionButtonId('sc-disconnect-action')
            ->render();
    }

    private function debug()
    {
        if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
            ?>
            <hr style="margin-top: 30px"><h3>Debug info</h3><p>
                smartcat_account_id: <?php echo empty(get_option('smartcat_account_id')) ? '<i>none</i>' : get_option('smartcat_account_id') ?>
            </p><p>
                smartcat_api_key: <?php echo empty(get_option('smartcat_api_key')) ? '<i>none</i>' : get_option('smartcat_api_key') ?>
            </p><p>
                smartcat_hub_key: <?php echo empty(get_option('smartcat_hub_key')) ? '<i>none</i>' : get_option('smartcat_hub_key') ?>
            </p><p>
                smartcat_api_host: <?php echo empty(get_option('smartcat_api_host')) ? '<i>none</i>' : get_option('smartcat_api_host') ?>
            </p><p>
                smartcat_hub_host: <?php echo empty(get_option('smartcat_hub_host')) ? '<i>none</i>' : get_option('smartcat_hub_host') ?>
            </p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="smartcat_auth_host"> <input type="hidden" name="smartcat_update_options_nonce" value="<?php echo wp_create_nonce('smartcat_update_options_form_nonce') ?>">
                <div>
                    <label for="debug-host"><b>Стартовый url хоста для авторизации в smartcat:</b></label> <input id="debug-host" type="text" name="host" placeholder="http://smartcat.stage.local" value="<?php echo SmartcatClient::getAuthHost() ?>">
                </div>
                <div>
                    <input id="debug-dev-mode" type="checkbox" name="debug"
                        <?php echo SmartcatClient::debugMode() ? 'checked' : '' ?>
                    > <label for="debug-dev-mode"> <b>dev mode (использует localhost):</b> </label>
                </div>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-secondary" value="Update">
                </p>
            </form>
            <?php
        }
    }

    private function migrations()
    {
        $migrationsFlag = $_GET['migrations'] ?? null;

        if ($migrationsFlag === 'run') {
            (new Migrations())->run();

            sc_ui()
                ->row()
                ->css(['margin-top' => '20px'])
                ->body(function () {
                    sc_ui()
                        ->text()
                        ->content('Migrations is done')
                        ->bold()
                        ->render();
                })->render();
        }
    }
}

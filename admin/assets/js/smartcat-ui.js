jQuery(document).ready(function ($) {
    class ScSelect {
        constructor() {
            // Init
            this.selectors = $('.sc-select');

            // Methods
            this.registerEvents()
        }

        registerEvents() {
            this.itemClick()
        }

        itemClick() {
            this.selectors
                .find('.sc-select__item')
                .on('click', function () {
                    const s = $(this).parent().parent()
                    const val = $(this).attr('value')
                    const text = $(this).text()
                    s.find('.sc-select__selected').text(text).show()
                    s.find('.sc-select__placeholder').hide()
                    s.attr('selected-item', val).blur()
                })
        }
    }

    class ScPopup {
        constructor() {
            this.popups = $('.sc-popup')

            this.registerEvents()
        }

        registerEvents() {
            this.close()
        }

        show(p) {
            p.fadeOut(100, function () {
                p.fadeIn(100);
            });
        }

        hide(p) {
            p.fadeOut(100);
        }

        close() {
            function hidePopup(p) {
                p.fadeOut(100);
            }

            this.popups
                .find('.sc-button.close')
                .on('click', function () {
                    const p = $(this).parent().parent().parent().parent()
                    hidePopup(p)
                })

            this.popups
                .find('.sc-button.close-icon')
                .on('click', function () {
                    const p = $(this).parent().parent().parent().parent()
                    hidePopup(p)
                })
        }
    }

    class ScConfirmPopup {
        constructor() {
            this.popups = $('.sc-confirm-popup')

            this.registerEvents()
        }

        registerEvents() {
            this.close()
        }

        close() {
            function hidePopup(p) {
                p.fadeOut(100);
            }

            this.popups
                .find('.sc-button.close')
                .on('click', function () {
                    const p = $(this).parent().parent().parent().parent()
                    hidePopup(p)
                })

            this.popups
                .find('.sc-button.cancel')
                .on('click', function () {
                    const p = $(this).parent().parent().parent().parent()
                    hidePopup(p)
                })
        }
    }

    class ScUrl {
        set(screen) {
            if (history.pushState) {
                let baseUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
                let newUrl = baseUrl + '?page=smartcat-wpml&screen=' + screen;
                history.pushState(null, null, newUrl);
            }
        }

        reset() {
            let baseUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
            let newUrl = baseUrl + '?page=smartcat-wpml';
            history.pushState(null, null, newUrl);
        }
    }

    class ScAnimation {
        fadeOut(el, dur = 100) {
            el.fadeIn(dur, function () {
                el.fadeOut(dur);
            });
        }

        fadeIn(el, dur = 100) {
            el.fadeOut(100, function () {
                el.fadeIn(100);
            });
        }
    }

    class ScScreen {
        integration = 'integration'
        app = 'app'

        to(screen = null) {
            switch (screen) {
                case this.integration:
                    scUrl.set(this.integration)
                    scAnimation.fadeOut(appScreen)
                    setTimeout(function () {
                        scAnimation.fadeIn(integrationScreen)
                    }, 100)
                    break
                default:
                    scUrl.set(this.app)
                    scAnimation.fadeOut(integrationScreen)
                    setTimeout(function () {
                        scAnimation.fadeIn(appScreen)
                    }, 100)
            }
        }
    }

    class ScNotice {
        notice = $('.sc-notification')
        success = 'success'
        danger = 'danger'

        constructor() {
            this.close()
        }

        show(content, type = this.success) {
            this.notice
                .find('.sc-notification__content')
                .removeClass('success')
                .removeClass('danger')
                .addClass(type)
                .html(content)

            scAnimation.fadeIn(this.notice)

            setTimeout(function () {
                scAnimation.fadeOut($('.sc-notification'))
            }.bind(this), 3000)
        }

        close() {
            this.notice.find('.sc-notification__close').click(function (e) {
                e.preventDefault()
                scAnimation.fadeOut($('.sc-notification'))
            })
        }
    }

    class ScButton {
        constructor() {
            this.handleHover()
        }

        loading(b) {
            b.addClass('loader')
        }

        unloading(b) {
            b.removeClass('loader')
        }

        disable(b) {
            b.prop('disabled', true);
        }

        undisable(b) {
            b.prop('disabled', false);
        }

        handleHover() {
            $('.sc-button-wrapper').hover(function () {
                if ($(this).find('.sc-tooltip').text().length > 0) {
                    $(this).find('.sc-tooltip').show()
                }

                if ($(this).find('.sc-button').prop('disabled')) {

                }
            }, function () {
                $(this).find('.sc-tooltip').hide()
            })
        }
    }

    class ScApi {
        generateSecret() {
            scButton.loading(generateNewKeyPopupActionBtn)

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'smartcat_new_secret',
                },
                success: function (data) {
                    scButton.unloading(generateNewKeyPopupActionBtn)
                    notMaskedSecretInput.val(data.secret)
                    let secret = data.secret
                    secret = secret.slice(0, -10)
                    secret = secret + '**********'
                    maskedSecret.val(secret)
                    scPopup.hide(generateNewKeyPopup)
                    scNotice.show('Key generated')
                },
                error: function () {
                    scButton.unloading(generateNewKeyPopupActionBtn)
                    scNotice.show('Unexpected error on the server')
                }
            });
        }

        registerCredentials() {
            scInput.unError(server)
            scInput.unError(accountId)
            scInput.unError(apiKey)

            scButton.loading(registerCredentialsActionBtn)

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'smartcat_register_credentials',
                    server: server.attr('selected-item'),
                    accountId: accountId.val(),
                    apiKey: apiKey.val()
                },
                success: function (data) {
                    scButton.unloading(registerCredentialsActionBtn)
                    scPopup.hide(accountParamsPopup)
                    $('#sc-account-name').text(`[${data.workspaceName} / ${data.organizationName}]`)
                    $('#sc-login').hide()
                    $('#sc-user').show()
                    scNotice.show('Account authentication completed!')
                },
                error: function (error) {
                    scButton.unloading(registerCredentialsActionBtn)

                    registerCredentialsActionBtn.find('.sc-tooltip').text('Please check your credentials')

                    if (error.status === 422) {
                        const fields = error.responseJSON.data;

                        for (const i in fields) {
                            scInput.error(
                                $(`.sc[name="${fields[i].field}"]`),
                                fields[i].message
                            );
                        }
                    } else {
                        scNotice.show('Unexpected error on the server')
                    }
                }
            });
        }

        saveSettings() {
            const isAlwaysNativeEditor = $('#sc-use-always-wp-editor').is(':checked');
            const isAutomaticallyGetTranslations = $('#sc-automatically-get-translations').is(':checked');
            const isDisableWpmlStringsRegister = $('#sc-disable-wpml-strings-register').is(':checked');
            const numberOfItemsToReceiveTranslations = $('#sc-number-of-items-to-receive-translations').val()

            scButton.loading(saveSettingsBtn)

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'smartcat_save_settings',
                    isAlwaysNativeEditor,
                    isAutomaticallyGetTranslations,
                    isDisableWpmlStringsRegister,
                    numberOfItemsToReceiveTranslations
                },
                success: function (data) {
                    scButton.unloading(saveSettingsBtn)
                    scNotice.show('Settings saved successfully!')
                },
                error: function (error) {
                    scButton.unloading(registerCredentialsActionBtn)
                    scNotice.show('Unexpected error on the server')
                }
            });
        }
    }

    class ScInput {
        error(i, m) {
            i.addClass('error')
                .siblings('.sc-input-error')
                .removeClass('sc-dn')
                .find('span')
                .text(m)
        }

        unError(i) {
            i.removeClass('error')
                .siblings('.sc-input-error')
                .addClass('sc-dn')
        }
    }

    class ScAccordion {
        constructor() {
            this.registerEvents();
        }

        registerEvents() {
            $('.sc-accordion__head').on('click', function () {
                $(this).parent('.sc-accordion').toggleClass('open');
            });
        }
    }

    // Init
    new ScSelect()
    new ScPopup()
    new ScConfirmPopup()
    // new ScAccordion()

    const
        // Buttons
        toIntegrationSettingsBtn = $('#sc-to-classic-integration'),
        toAppSettingsBtn = $('#sc-to-app-screen'),
        authenticateManuallyBtn = $('#sc-authenticate-manually'),
        copySecretBtn = $('#sc-copy-secret'),
        generateNewKeyBtn = $('#sc-generate-new-key'),
        generateNewKeyPopupActionBtn = $('#sc-generate-new-key-popup-action'),
        saveSettingsBtn = $('#sc-save-settings-button'),
        connectToSmartcatBtn = $('#sc-connect-to-smartcat'),
        registerCredentialsActionBtn = $('#sc-register-credentials-action'),
        disconnectAction = $('#sc-disconnect-action'),
        disconnectBtn = $('#sc-disconnect-btn'),
        // Screens
        appScreen = $('#sc-app-screen'),
        integrationScreen = $('#sc-integration-screen'),
        // Popups
        accountParamsPopup = $('#sc-account-params-popup'),
        generateNewKeyPopup = $('#sc-generate-new-key-popup'),
        disconnectConfirmPopup = $('#sc-disconnect-confirm-popup'),
        // Inputs
        notMaskedSecretInput = $('#sc-not-masked-secret'),
        server = $('#sc-server'),
        accountId = $('#sc-account-id'),
        apiKey = $('#sc-api-key'),
        // Values
        maskedSecret = $('#sc-masked-secret'),
        // Forms
        disconnectForm = $('#sc-disconnect-form'),
        // Services
        scPopup = new ScPopup(),
        scUrl = new ScUrl(),
        scAnimation = new ScAnimation(),
        scScreen = new ScScreen(),
        scNotice = new ScNotice(),
        scApi = new ScApi(),
        scButton = new ScButton(),
        scInput = new ScInput()

    // Events

    // Show app credentials popup
    authenticateManuallyBtn.on('click', function () {
        scPopup.show(accountParamsPopup)
    })

    // Move to classic integration screen
    toIntegrationSettingsBtn.on('click', function () {
        scScreen.to(scScreen.integration)
    })

    // Move to app screen
    toAppSettingsBtn.on('click', function () {
        scScreen.to() // default < app >
    })

    // Copy secret
    copySecretBtn.on('click', function (e) {
        e.preventDefault()
        scCopy(notMaskedSecretInput.val())
        scNotice.show('Key copied')
    })

    // Show popup for new key generationg
    generateNewKeyBtn.on('click', function () {
        scPopup.show(generateNewKeyPopup)
    })

    // Generate new key action (call Ajax)
    generateNewKeyPopupActionBtn.on('click', function () {
        scApi.generateSecret()
    })

    // Save settings
    saveSettingsBtn.on('click', function () {
        scApi.saveSettings();
    })

    // Auth in Smartcat (just loading)
    connectToSmartcatBtn.on('click', function () {
        scButton.loading($(this))
    })

    // Register credentials in Smartcat
    registerCredentialsActionBtn.on('click', function () {
        scApi.registerCredentials()
    })

    // Show disconnect popup
    disconnectBtn.on('click', function (e) {
        e.preventDefault()
        scPopup.show(disconnectConfirmPopup)
    })

    // Disconnect action
    disconnectAction.on('click', function (e) {
        scButton.loading(disconnectAction)
        disconnectForm.submit()
    })

    // Validation credentials form
    scButton.disable(registerCredentialsActionBtn);
    accountId.on('keyup', validateCredentialsForm)
    apiKey.on('keyup', validateCredentialsForm)
    $('.sc-select .sc-select__item').on('click', validateCredentialsForm)


    // Functions / Helpers

    function scCopy(text) {
        let temp = $(`<input id="sc-temp-${Math.random()}">`);
        $('body').append(temp);
        temp.val(text).select();
        document.execCommand('copy');
        temp.remove();
    }

    function validateCredentialsForm() {
        let errorsCount = 0

        if (scIsBlank(accountId.val())) {
            // scInput.error(accountId, 'Please check your account ID and try again')
            errorsCount++;
        } else {
            scInput.unError(accountId)
        }

        if (scIsBlank(apiKey.val())) {
            // scInput.error(apiKey, 'Please check your API key and try again')
            errorsCount++;
        } else {
            scInput.unError(apiKey)
        }

        if (server.attr('selected-item') === undefined) {
            // scInput.error(server, 'Please select a Smartcat server')
            errorsCount++;
        } else {
            scInput.unError(server)
        }

        if (errorsCount === 0) {
            scButton.undisable(registerCredentialsActionBtn)
            registerCredentialsActionBtn.find('.sc-tooltip').text('')
        } else {
            scButton.disable(registerCredentialsActionBtn)
            registerCredentialsActionBtn.find('.sc-tooltip').text('Please fill out all required fields')
        }
    }

    function scIsBlank(str) {
        return (!str || /^\s*$/.test(str));
    }
})
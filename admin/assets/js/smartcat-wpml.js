jQuery(document).ready(function ($) {
        $('body').append(`
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
    `)

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

        class ScNotice {
            notice = $('.sc-notification')
            success = 'success'
            danger = 'danger'

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
                }.bind(this), 5000)
            }

            close() {
                this.notice.find('.sc-notification__close').click(function (e) {
                    e.preventDefault()
                    scAnimation.fadeOut($('.sc-notification'))
                })
            }
        }

        let defaultErrorMessage, postIds, sourceLanguage, translationRequestId, languages,
            languagesInTranslationRequest, projectsSelector, deadlineInput, commentInput,
            sendToSmartcatButton, getTranslationsButton, updateSourceContentButton,
            removeTranslationRequestButton, getTranslationsForPostButton, errorNotice,
            successNotice, warnNotice, infoNotice, popup, removePostFromTrButton,
            workflowStages, scAnimation, scNotice, projectsLimit, projectsOffset,
            projectsLoading, projectsLoadingIsComplete, dashboardDoAction, translationRequestsDeleting,
            translationRequestRows, updateTrProgressButton, sendingPostsLoading,
            translationRequestsReceivingTranslations, translationRequestsReceivingTranslationsStatus,
            processedTranslationRequest, wpmlLanguages, addLanguageToTranslationRequest,
            removeLanguageFromTranslationRequest, postsInTranslationRequest, updatePostsInSmartcatButton,
            getTranslationsProgress, itemsToImportQueue, itemsInProgressList, tasksInProgressElement,
            numberOfItemsToReceiveTranslationsInput

        function registerEvents() {
            // project selector event
            projectsSelector.on('change', projectSelectorListener)

            // languages event
            getLanguages().on('change', languagesListener)

            // deadline input event
            deadlineInput.on('change', deadlineListener)

            // send to Smartcat button event
            sendToSmartcatButton.on('click', function () {
                sendToSmartcatBatch()
            })

            // console.log(postIds, getSelectedLanguageCodes())

            // languages change
            languagesInTranslationRequest.on('change', function () {
                const inTranslationRequest = $(this).attr('in-request');

                const data = {
                    language: $(this).val(),
                    postId: $(this).attr('post-id'),
                    translationRequestId: translationRequestId
                }

                if (inTranslationRequest === 'true' && !$(this).is('checked')) {
                    $(this).prop('checked', true);
                    showPopup(`Do you want to remove <span class="smartcat__selection">${jQuery(this).attr('language-name')}</span> language from an
                already published translation request?`, 'removeLanguage', data)
                } else {
                    $(this).prop('checked', false);
                    showPopup(`Do you want to add <span class="smartcat__selection">${jQuery(this).attr('language-name')}</span> language 
            to an already published translation request?`, 'addLanguage', data)
                }
            })

            // get translations event
            getTranslationsButton.on('click', getTranslationsFromPost)

            // Update source content event
            updateSourceContentButton.on('click', updateSourceContent)

            // remove translation request event
            removeTranslationRequestButton.on('click', function () {
                showPopup(`Do you want to delete an entire translation request?`, 'removeTranslationRequest', {
                    translationRequestId: $(this).attr('smartcat-tr-id').trim()
                })
            })

            $('.smartcat-get-translations-from-dashboard').on('click', function () {
                const trId = $(this).attr('smartcat-tr-id')
                $(`#smartcat-actions-${trId}`).hide(0)
                $(`#smartcat-spin-${trId}`).show(0)

                const translationRequestName = $(`#sc-tr-name-${trId}`).text()
                const posts = JSON.parse($(`#sc-tr-posts-${trId}`).text());
                const locales = JSON.parse($(`#sc-tr-locales-${trId}`).text());

                for (const postId in posts) {
                    for (const locale of locales) {
                        itemsToImportQueue.push({
                            translationRequestId: trId,
                            translationRequestName,
                            postId,
                            locale
                        })
                    }
                }

                getTranslationsFromQueue()
            })

            // Remove post from TR event
            removePostFromTrButton.on('click', function (e) {
                e.preventDefault()
                showPopup(`Are you sure you want to remove this article from the translation request?`, 'removePostFromTR', {
                    translationRequestId: translationRequestId,
                    postId: $(this).attr('post-id')
                })
            })

            // get translations for post event
            getTranslationsForPostButton.on('click', function (e) {
                e.preventDefault()
                const postId = $(this).attr('post-id');
                $(`#smartcat-actions-${postId}`).hide(0)
                $(`#smartcat-dropdown-menu-${postId}`).hide(0)
                $(`#smartcat-spin-${postId}`).show(0)
                getTranslations(e, postId, translationRequestId)
            })

            popup.find('.confirm').on('click', handlePopupConfirmButton);
            popup.find('.cancel').on('click', hidePopup);

            $('.smartcat-show-event-data').on('click', function (event) {
                event.preventDefault();
                $('#smartcat-logs-dialog').html($(this).find('.data').html());
                $('#smartcat-logs-dialog').dialog();
            })

            // Projects selector events
            $('.sc-dropdown__items--item').click(function (e) {
                e.preventDefault()
                selectSmartcatProject(this)
            })

            $('.sc-dropdown__selector').click(function (e) {
                e.preventDefault()
            })

            $('.sc-dropdown__items').scroll(function () {

                if (($(this).scrollTop() + $(this).innerHeight() + 1) >= $(this)[0].scrollHeight) {
                    if (!projectsLoading && !projectsLoadingIsComplete) {
                        fetchSmatcatProjects()
                    }
                }
            })

            projectsSelector.find('.sc-dropdown__search').on('keyup', findProjectsByName)

            projectsSelector.find('.sc-dropdown__search').focus(function () {
                projectsSelector.addClass('is-show')
            })

            projectsSelector.find('.sc-dropdown__search').focusout(function () {
                projectsSelector.removeClass('is-show')
            })

            dashboardDoAction.on('click', async function () {
                switch ($('#bulk-action-selector-top').val()) {
                    case 'delete':
                        await deleteSelectedTranslationRequests()
                        break
                    case 'get_translations':
                        await getTranslationsForSelectedRequests()
                        break
                }
            })

            updateTrProgressButton.on('click', function (e) {
                e.preventDefault();
                updateTranslationRequestStatus($(this).attr('tr-id'))
            })

            $('#sc-update-post-info').on('click', function (e) {
                e.preventDefault()

                showMetaboxLoading()
                updateMetabox()
            })

            addLanguageToTranslationRequest.on('click', addLanguagesToTranslationRequest)

            removeLanguageFromTranslationRequest.on('click', removeLanguagesFromTranslationRequest);

            updatePostsInSmartcatButton.on('click', updateAllSourcePostsContent);
        }

        function initVariables() {
            defaultErrorMessage = 'Unexpected error. Check the error log.'
            postIds = $('#smartcat-post-id').val()
            sourceLanguage = $('#smartcat-source-language').val()
            translationRequestId = $('#smartcat-tr-id').val()
            languages = $('.smartcat-language')
            languagesInTranslationRequest = $('.smartcat-language-with-tr')
            projectsSelector = $('#sc-projects-selector')
            deadlineInput = $('#smartcat-deadline-input')
            commentInput = $('#smartcat-comment-input')
            sendToSmartcatButton = $('#send-to-smartcat-button')
            getTranslationsButton = $('#smartcat-get-translations')
            updateSourceContentButton = $('#update-source-content-button')
            removeTranslationRequestButton = $('.smartcat-delete-tr-button')
            getTranslationsForPostButton = $('.smartcat-get-translations-action')
            errorNotice = $('#smarcat-notice-error')
            successNotice = $('#smarcat-notice-success')
            warnNotice = $('#smarcat-notice-warn')
            infoNotice = $('#smarcat-notice-info')
            popup = $('.smartcat__popup--wrapper')
            removePostFromTrButton = $('.smartcat-remove-post-action')
            workflowStages = $('#sc-workflow-stage')
            scAnimation = new ScAnimation()
            scNotice = new ScNotice()
            projectsLimit = 100
            projectsOffset = projectsLimit
            projectsLoading = false
            projectsLoadingIsComplete = false
            dashboardDoAction = $('.dashboard #doaction')
            translationRequestsDeleting = $('.sc-translation-requests-deleting')
            translationRequestRows = $('.sc-tr-row')
            updateTrProgressButton = $('.sc-update-tr-progress')
            sendingPostsLoading = $('.sc-sending-posts-to-smartcat')
            translationRequestsReceivingTranslations = $('.sc-translation-request-receiving')
            translationRequestsReceivingTranslationsStatus = translationRequestsReceivingTranslations.find('.sc-translation-request-receiving-status')
            processedTranslationRequest = {}
            wpmlLanguages = $('.sc-wpml-language')
            addLanguageToTranslationRequest = $('#sc-add-languages-to-translation-request')
            removeLanguageFromTranslationRequest = $('#sc-remove-languages-from-translation-request')
            postsInTranslationRequest = $('.sc-post-in-translation-request');
            updatePostsInSmartcatButton = $('#sc-update-posts-in-smartcat-button');
            getTranslationsProgress = $('.sc-get-translations-progress')
            itemsToImportQueue = [];
            itemsInProgressList = $('#sc-items-in-progress');
            tasksInProgressElement = $('#sc-tasks-in-progress');
            numberOfItemsToReceiveTranslationsInput = $('#sc-number-of-items-to-receive-translations-input').val() || 5;
        }

        initVariables()
        registerEvents()

        // -- Send to Smartcat --

        // project selector listener
        function projectSelectorListener(hasParentEvent = false) {
            const selectedProject = getSelectedProject()

            if (getSelectedProjectId() === 'new' && getSelectedLanguageCodes().length === 0) {
                disable(sendToSmartcatButton)
                return
            }

            disableOff(sendToSmartcatButton)
        }

        // languages listener
        function languagesListener() {
            projectSelectorListener(true);
        }

        // deadline input listener
        function deadlineListener() {
            const selectedProject = getSelectedProject();
            if (selectedProject.attr('sc-deadline') && deadlineInput.val()) {
                showNotice(
                    'warn',
                    `Project <b>${selectedProject.text()}</b> already has a deadline of <b>${new Date(selectedProject.attr('sc-deadline')).toGMTString()}</b>.
                        After creating a translation request, it will be changed to the current one.`,
                    false
                );
            } else {
                hideAllNotices();
            }
        }

        // send to Smartcat button action/listener
        function sendToSmartcat() {
            buttonLoadState(sendToSmartcatButton)

            const data = {
                action: 'smartcat_create_translation_request',
                postId: postIds,
                sourceLanguage: sourceLanguage,
                targetLanguages: getSelectedLanguageCodes(),
                projectId: getSelectedProjectId() === 'new' ? null : getSelectedProjectId(),
                deadline: deadlineInput.val(),
                comment: commentInput.val(),
                workflowStage: getSelectedProjectId() === 'new' ? workflowStages.val() : null
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    if (data.status === 'failed') {
                        showNotice('error', data.message)
                        buttonNormalState(sendToSmartcatButton)
                        return;
                    }
                    scNotice.show(`Translation request successfully created`)

                    showMetaboxLoading()
                    updateMetabox()
                    buttonNormalState(sendToSmartcatButton)
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                    buttonNormalState(sendToSmartcatButton)
                }
            });
        }

        async function sendToSmartcatBatch() {
            buttonLoadState(sendToSmartcatButton)

            const postIdList = isCratingPage() ? JSON.parse(postIds) : [parseInt(postIds)];
            const targetLanguages = getSelectedLanguageCodes();

            let translationRequestId = null
            let batch = {};

            for (const postId of postIdList) {
                batch[postId] = targetLanguages
            }

            const firstPostId = postIdList[0];
            const firstLanguage = targetLanguages[0];


            fadeIn(sendingPostsLoading, 0)

            updateSendingPostsLoader(firstPostId, firstLanguage)
            batch = removeLocaleFromBatch(batch, firstPostId, firstLanguage)

            const data = {
                action: 'smartcat_create_translation_request',
                postId: JSON.stringify([firstPostId]),
                sourceLanguage: sourceLanguage,
                targetLanguages: [firstLanguage],
                projectId: getSelectedProjectId() === 'new' ? null : getSelectedProjectId(),
                deadline: deadlineInput.val(),
                comment: commentInput.val(),
                workflowStage: getSelectedProjectId() === 'new' ? workflowStages.val() : null
            };

            await jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    if (data.status === 'failed') {
                        showNotice('error', data.message)
                        buttonNormalState(sendToSmartcatButton)
                        return;
                    }

                    translationRequestId = data.data.id
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                    buttonNormalState(sendToSmartcatButton)
                }
            });

            if (translationRequestId === null) {
                return;
            }

            let hasErrorsFromLastRequest = false;
            let errorMessage = null;

            for (const postId in batch) {
                if (hasErrorsFromLastRequest) {
                    break
                }

                for (const locale of batch[postId]) {
                    if (hasErrorsFromLastRequest) {
                        break
                    }

                    updateSendingPostsLoader(postId, locale)

                    const data = {
                        action: 'smartcat_add_language_to_translation_request',
                        postId: postId,
                        translationRequestId: translationRequestId,
                        language: locale,
                    };

                    try {
                        let res = await jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: data,
                        });

                        if (res.status === 'failed') {
                            hasErrorsFromLastRequest = true
                            errorMessage = res.message
                        }
                    } catch (e) {
                        hasErrorsFromLastRequest = true
                        errorMessage = defaultErrorMessage
                    }
                }
            }

            fadeOut(sendingPostsLoading, 0)

            if (!hasErrorsFromLastRequest && isCratingPage()) {
                document.location.href = `/wp-admin/admin.php?page=smartcat-wpml-translation-request&id=${translationRequestId}`
            } else if (hasErrorsFromLastRequest === false) {
                scNotice.show(`Translation request successfully created`)
                showMetaboxLoading()
                updateMetabox()
            } else {
                showNotice('error', errorMessage);
                buttonNormalState(sendToSmartcatButton);
            }
        }

        async function addLanguagesToTranslationRequest() {
            const postIds = getTranslationRequestPostIds();
            const locales = getSelectedWpmlLocales();
            const totalEvents = postIds.length * locales.length;

            disableTranslationRequestElements()

            let hasErrorsFromLastRequest = false;
            let errorMessage = null;

            fadeIn(sendingPostsLoading, 0)

            let currentEventNumber = 1;

            for (const postId of postIds) {
                if (hasErrorsFromLastRequest) {
                    break
                }

                for (const locale of locales) {
                    if (hasErrorsFromLastRequest) {
                        break
                    }

                    updateSendingPostsLoader(postId, locale, `(${currentEventNumber} of ${totalEvents})`)

                    const data = {
                        action: 'smartcat_add_language_to_translation_request',
                        postId: postId,
                        translationRequestId: translationRequestId,
                        language: locale,
                    };

                    try {
                        let res = await jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: data,
                        });

                        if (res.status === 'failed') {
                            hasErrorsFromLastRequest = true
                            errorMessage = res.message
                        } else {
                            currentEventNumber++;
                            $(`input[post-id="${postId}"][value="${locale}"]`).prop('checked', true)
                        }
                    } catch (e) {
                        hasErrorsFromLastRequest = true
                        errorMessage = defaultErrorMessage
                    }
                }
            }

            fadeOut(sendingPostsLoading, 0)

            if (hasErrorsFromLastRequest) {
                showNotice('error', errorMessage);
            } else {
                scNotice.show(`New languages have been successfully added to the translation request`)
            }

            enableTranslationRequestElements()
        }

        async function removeLanguagesFromTranslationRequest() {
            const postIds = getTranslationRequestPostIds();
            const locales = getSelectedWpmlLocales();
            const totalEvents = postIds.length * locales.length;

            disableTranslationRequestElements()

            let hasErrorsFromLastRequest = false;
            let errorMessage = null;

            fadeIn(sendingPostsLoading, 0)

            let currentEventNumber = 1;

            for (const postId of postIds) {
                if (hasErrorsFromLastRequest) {
                    break
                }

                for (const locale of locales) {
                    if (hasErrorsFromLastRequest) {
                        break
                    }

                    updateSendingPostsLoader(postId, locale, `(${currentEventNumber} of ${totalEvents})`)

                    const data = {
                        action: 'smartcat_remove_language',
                        postId: postId,
                        language: locale,
                    };

                    try {
                        let res = await jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: data,
                        });

                        if (res.status === 'failed') {
                            hasErrorsFromLastRequest = true
                            errorMessage = res.message
                        } else {
                            currentEventNumber++;
                            $(`input[post-id="${postId}"][value="${locale}"]`).prop('checked', false)
                        }
                    } catch (e) {
                        hasErrorsFromLastRequest = true
                        errorMessage = defaultErrorMessage
                    }
                }
            }

            fadeOut(sendingPostsLoading, 0)

            if (hasErrorsFromLastRequest) {
                showNotice('error', errorMessage);
            } else {
                scNotice.show(`The selected languages have been successfully removed from the translation request`)
            }

            enableTranslationRequestElements()
        }

        function getTranslationRequestPostIds() {
            const postIds = [];
            postsInTranslationRequest.each(function () {
                postIds.push($(this).attr('sc-post-id'))
            });
            return postIds;
        }

        function getSelectedWpmlLocales() {
            const locales = [];
            wpmlLanguages.each(function () {
                if ($(this).prop('checked')) {
                    locales.push($(this).val())
                }
            });
            return locales;
        }

        function disableTranslationRequestElements() {
            addLanguageToTranslationRequest.prop('disabled', true);
            removeLanguageFromTranslationRequest.prop('disabled', true);
            wpmlLanguages.prop('disabled', true);
        }

        function enableTranslationRequestElements() {
            addLanguageToTranslationRequest.prop('disabled', false);
            removeLanguageFromTranslationRequest.prop('disabled', false);
            wpmlLanguages.prop('disabled', false);
        }

        function updateSendingPostsLoader(postId, locale, additionalText = '') {
            const postName = $(`a[sc-post-id="${postId}"]`).text()
            sendingPostsLoading.find('.sc-sending-post-name').html(`Sending: <b>${postName}</b> (${locale}) ${additionalText}`)
        }

        function removeLocaleFromBatch(batch, postId, locale) {
            batch[postId] = batch[postId].filter(x => x !== locale)
            return batch;
        }

        function isCratingPage() {
            return document.location.href.includes('smartcat-wpml-create-translation-request')
        }

        // -- Update information --

        function updateMetabox() {
            $('#smartcat_metabox')
                .load(' #smartcat_metabox > *', function () {
                    initVariables()
                    registerEvents()
                });
        }

        // -- Languages change --

        // remove language action
        function removeLanguage() {
            const popupData = getPopupData();

            const data = {
                action: 'smartcat_remove_language',
                postId: popupData.postId,
                language: popupData.language,
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    hidePopup();

                    const checkbox = $(`.smartcat-language-with-tr[post-id="${popupData.postId}"].smartcat-language-with-tr[value="${popupData.language}"]`)
                    checkbox.prop('checked', false)
                    scNotice.show(`Language ${checkbox.attr('language-name')} has been successfully removed`)

                    showMetaboxLoading()
                    updateMetabox()
                },
                error: function () {
                    hidePopup();
                    showNotice('error', defaultErrorMessage);
                }
            });
        }

        // add language action
        function addLanguage() {
            const popupData = getPopupData();

            const data = {
                action: 'smartcat_add_language_to_translation_request',
                postId: popupData.postId,
                translationRequestId: popupData.translationRequestId,
                language: popupData.language,
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    if (data.status === 'failed') {
                        hidePopup()
                        showNotice('error', data.message)
                        return
                    }

                    const checkbox = $(`.smartcat-language-with-tr[post-id="${popupData.postId}"].smartcat-language-with-tr[value="${popupData.language}"]`)
                    checkbox.prop('checked', true)

                    hidePopup()
                    scNotice.show(`Language ${checkbox.attr('language-name')} has been added successfully`)
                    showMetaboxLoading()
                    updateMetabox()
                },
                error: function () {
                    hidePopup();
                    showNotice('error', defaultErrorMessage);
                }
            });
        }

        // -- Get from Smartcat --

        function messageGetTranslationsProgress(message) {
            getTranslationsProgress.find('.sc-get-translations-name').html(message)
        }

        async function getTranslationsFromPost() {
            buttonLoadState(getTranslationsButton)
            getTranslationsProgress.show(0)

            const isSkipImportingPackageStrings = $('#sc-skip-packages-import').is(':checked');

            const postId = postIds;

            const languages = [];

            $(`.smartcat-language-with-tr[post-id="${postId}"]`).filter(function () {
                return $(this).is(':checked')
            }).each(function () {
                languages.push({
                    name: $(this).attr('language-name'),
                    locale: $(this).val()
                });
            })

            for (const lang of languages) {
                messageGetTranslationsProgress(`Importing: <b>${lang.name}</b>`)

                const data = {
                    action: 'smartcat_get_translations_by_post_and_locale',
                    postId,
                    translationRequestId,
                    isSkipImportingPackageStrings,
                    locale: lang.locale
                };

                await jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    success: function (data) {
                        if (data.status === 'failed') {
                            showNotice('error', data.message)
                            buttonNormalState(getTranslationsButton)
                        } else {
                            const message = 'Translations successfully imported into WordPress';

                            scNotice.show(message)
                            showNotice('success', message)

                            buttonNormalState(getTranslationsButton)
                        }
                    },
                    error: function () {
                        showNotice('error', defaultErrorMessage)
                        buttonNormalState(getTranslationsButton)
                    }
                });
            }

            getTranslationsProgress.hide(0)
        }

        async function getTranslations(e, cPostIds = false, cTranslationRequestId = false, withoutNotification = false) {
            buttonLoadState(getTranslationsButton)

            const isSkipImportingPackageStrings = $('#sc-skip-packages-import').is(':checked');

            const data = {
                action: 'smartcat_get_translations',
                postIds: !cPostIds ? postIds : cPostIds,
                translationRequestId: !cTranslationRequestId ? translationRequestId : cTranslationRequestId,
                skipImportingPackageStrings: isSkipImportingPackageStrings
            };

            await jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    if (data.status === 'failed') {
                        showNotice('error', data.message)
                        buttonNormalState(getTranslationsButton)
                        return;
                    } else {
                        if (!withoutNotification) {
                            scNotice.show(`Translations successfully imported into WordPress`)
                        }

                        buttonNormalState(getTranslationsButton)

                        if (cTranslationRequestId) {
                            $(`#smartcat-actions-${cTranslationRequestId}`).show(0)
                            $(`#smartcat-spin-${cTranslationRequestId}`).hide(0)
                            $(`#smartcat-actions-${cPostIds}`).show(0)
                            $(`#smartcat-dropdown-menu-${cPostIds}`).show(0)
                            $(`#smartcat-spin-${cPostIds}`).hide(0)
                        }

                        showMetaboxLoading()
                        updateMetabox()
                    }
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                    buttonNormalState(getTranslationsButton)

                    if (cTranslationRequestId) {
                        $(`#smartcat-actions-${cTranslationRequestId}`).show(0)
                        $(`#smartcat-spin-${cTranslationRequestId}`).hide(0)
                        $(`#smartcat-dropdown-menu-${cPostIds}`).show(0)
                        $(`#smartcat-actions-${cPostIds}`).show(0)
                        $(`#smartcat-spin-${cPostIds}`).hide(0)
                    }
                }
            });
        }

        function getTranslationsForSelectedRequests() {
            let ids = [];

            $('input[name="tr[]"]:checked').each(function (i) {
                ids[i] = $(this).val();
            })

            for (const id of ids) {
                $(`#smartcat-actions-${id}`).hide(0)
                $(`#smartcat-spin-${id}`).show(0)
            }

            for (const id of ids) {
                const posts = JSON.parse($(`#sc-tr-posts-${id}`).text());
                const locales = JSON.parse($(`#sc-tr-locales-${id}`).text());
                const translationRequestName = $(`#sc-tr-name-${id}`).text()

                for (const postId in posts) {
                    for (const locale of locales) {
                        itemsToImportQueue.push({
                            translationRequestId: id,
                            translationRequestName,
                            postId,
                            locale
                        })
                    }
                }
            }

            getTranslationsFromQueue()
        }

        function getTranslationsByTranslationRequest(translationRequestId, locales, posts) {
            const translationRequestName = $(`#sc-tr-name-${translationRequestId}`).text()

            // fadeIn(translationRequestsReceivingTranslations)

            for (const postId in posts) {
                for (const locale of locales) {
                    // translationRequestsReceivingTranslationsStatus
                    //     .html(`Import translations for <b>${translationRequestName}</b>: ${posts[postId]} (${locale})`)

                    const data = {
                        action: 'smartcat_get_translations_by_post_and_locale',
                        postId: postId,
                        translationRequestId: translationRequestId,
                        locale: locale
                    };

                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data,
                        success: function (data) {
                            if (data.status === 'failed') {
                                showNotice('error', data.message)
                                buttonNormalState(getTranslationsButton)
                            }
                            processedTranslationRequest[translationRequestId].processedPostsCount += 1;
                        },
                        error: function () {
                            processedTranslationRequest[translationRequestId].processedPostsCount += 1;
                            showNotice('error', defaultErrorMessage)
                        }
                    });
                }
            }

            let waiter = setInterval(function () {
                const isCompleted = processedTranslationRequest[translationRequestId].processedPostsCount === processedTranslationRequest[translationRequestId].needProcessedPostsCount;

                if (isCompleted) {
                    // fadeOut(translationRequestsReceivingTranslations)
                    $(`#smartcat-actions-${translationRequestId}`).show(0)
                    $(`#smartcat-spin-${translationRequestId}`).hide(0)
                    scNotice.show(`Importing translations for ${translationRequestName} is completed`)
                    clearInterval(waiter)
                }
            }, 100)
        }

        function getTranslationsFromQueue() {
            if (itemsToImportQueue.length === 0) {
                scNotice.show(`All translations have been successfully imported`)
                hideTasksInProgress()
                return;
            }

            showTasksInProgress();

            const batchItemsCount = numberOfItemsToReceiveTranslationsInput;
            let completedItemsCount = 0;

            const items = itemsToImportQueue.splice(0, batchItemsCount);

            for (const item of items) {
                const {translationRequestId, translationRequestName, postId, locale} = item;

                const data = {
                    action: 'smartcat_get_translations_by_post_and_locale',
                    postId,
                    translationRequestId,
                    locale
                };

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    beforeSend: function () {
                        translationRequestLoader(translationRequestId)
                        appendItemProgress(translationRequestName, postId, locale)
                    },
                    success: function (data) {
                        if (data.status === 'failed') {
                        }

                        completedItemsCount++;
                        removeItemProgress(postId, locale)
                        translationRequestLoader(translationRequestId, false)
                        minusTasksInProgress()
                    },
                    error: function () {
                        completedItemsCount++;
                        removeItemProgress(postId, locale)
                        translationRequestLoader(translationRequestId, false)
                        minusTasksInProgress()
                    }
                });
            }

            let listener = setInterval(function () {
                if (completedItemsCount === batchItemsCount || items.length === completedItemsCount) {
                    clearInterval(listener)
                    getTranslationsFromQueue()
                }
            }, 100);
        }

        function appendItemProgress(translationRequestName, postId, locale) {
            itemsInProgressList.append(`
            <div class="sc sc-row sc-df sc-aic sc-translation-request-receiving" style="margin-bottom: 5px;" id="sc-item-${postId}_${locale}">
                <div class="sc-loader color"><div></div><div></div><div></div><div></div></div>
                <span class="sc sc-text sc-translation-request-receiving-status" style="font-size: 12px;">Loading translations: ${translationRequestName}: PostID - ${postId} (${locale})</span>
            </div>
        `);
        }

        function removeItemProgress(postId, locale) {
            $(`#sc-item-${postId}_${locale}`).remove();
        }

        function translationRequestLoader(translationRequestId, isEnabled = true) {
            if (isEnabled) {
                $(`#smartcat-actions-${translationRequestId}`).hide(0)
                $(`#smartcat-spin-${translationRequestId}`).show(0)
            } else {
                $(`#smartcat-actions-${translationRequestId}`).show(0)
                $(`#smartcat-spin-${translationRequestId}`).hide(0)
            }
        }

        function showTasksInProgress() {
            initTasksInProgress();
            tasksInProgressElement.show(0)
        }

        function hideTasksInProgress() {
            tasksInProgressElement.hide(0)
        }

        function initTasksInProgress() {
            tasksInProgressElement.find('.value').text(itemsToImportQueue.length)
        }

        function minusTasksInProgress(count = 1) {
            const currentCount = parseInt(tasksInProgressElement.find('.value').text())
            tasksInProgressElement.find('.value').text(currentCount - count)
        }


        // -- Update source content

        async function updateSourceContent(e, cPostIds = false, cTranslationRequestId = false) {
            buttonLoadState(updateSourceContentButton)

            fadeIn(sendingPostsLoading, 0)

            const postId = postIds;
            let hasErrorsFromLastRequest = false;
            let errorMessage = null;

            const locales = languagesInTranslationRequest.filter(function () {
                return $(this).is(':checked')
            }).map(function () {
                return $(this).val()
            })

            for (const locale of locales.get()) {
                if (hasErrorsFromLastRequest) {
                    break
                }

                updateSendingPostsLoader(postId, locale)

                const data = {
                    action: 'smartcat_update_source_content',
                    postId: postId,
                    locale: locale,
                    translationRequestId: !cTranslationRequestId ? translationRequestId : cTranslationRequestId
                };

                const res = await jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data
                });

                if (res.status === 'failed') {
                    hasErrorsFromLastRequest = true
                    errorMessage = res.message
                }
            }

            fadeOut(sendingPostsLoading, 0)
            buttonNormalState(updateSourceContentButton);

            if (hasErrorsFromLastRequest) {
                showNotice('error', errorMessage);
                buttonNormalState(sendToSmartcatButton);
            } else {
                scNotice.show(`Source content updated successfully`)
                showMetaboxLoading()
                updateMetabox()
            }
        }

        async function updateAllSourcePostsContent() {
            buttonLoadState(updatePostsInSmartcatButton);

            fadeIn(sendingPostsLoading, 0)

            const postIds = [];

            postsInTranslationRequest.each(function () {
                postIds.push($(this).attr('sc-post-id'));
            })

            let hasErrorsFromLastRequest = false;
            let errorMessage = null;

            for (const postId of postIds) {
                if (hasErrorsFromLastRequest) {
                    break
                }

                const locales = [];

                $(`.smartcat-language-with-tr[post-id="${postId}"]`).filter(function () {
                    return $(this).is(':checked')
                }).each(function () {
                    locales.push($(this).val());
                })

                for (const locale of locales) {
                    if (hasErrorsFromLastRequest) {
                        break
                    }

                    updateSendingPostsLoader(postId, locale)

                    const data = {
                        action: 'smartcat_update_source_content',
                        postId: postId,
                        locale: locale,
                        translationRequestId: translationRequestId
                    };

                    const res = await jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data
                    });

                    if (res.status === 'failed') {
                        hasErrorsFromLastRequest = true
                        errorMessage = res.message
                    }
                }
            }

            fadeOut(sendingPostsLoading, 0)
            buttonNormalState(updatePostsInSmartcatButton);

            if (hasErrorsFromLastRequest) {
                showNotice('error', errorMessage);
                buttonNormalState(updatePostsInSmartcatButton);
            } else {
                scNotice.show(`Source content updated successfully`)
            }
        }

        // -- Dashboard --

        function removeTranslationRequest() {
            const translationRequestId = getPopupData().translationRequestId;

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'smartcat_remove_translation_request',
                    trId: translationRequestId
                },
                success: function (data) {
                    const row = $(`.sc-tr-row[sc-tr-id="${translationRequestId}"]`)

                    hidePopup()

                    fadeOut(translationRequestsDeleting, 0)

                    row.addClass('smartcat__error-row')
                    fadeOut(row, 500)
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                    fadeOut(translationRequestsDeleting, 0)
                    hidePopup()
                }
            });
        }

        async function deleteSelectedTranslationRequests() {
            let ids = [];

            $('input[name="tr[]"]:checked').each(function (i) {
                ids[i] = $(this).val();
            })

            translationRequestsDeleting
                .find('.sc-translation-request-name')
                .html(`Deleting translation requests`)

            fadeIn(translationRequestsDeleting, 0)

            for (const id of ids) {
                await jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'smartcat_remove_translation_request',
                        trId: id
                    },
                    success: function (data) {
                        const row = $(`.sc-tr-row[sc-tr-id="${id}"]`)

                        console.log(row)

                        row.addClass('smartcat__error-row')
                        fadeOut(row, 500)
                    },
                    error: function () {
                        showNotice('error', defaultErrorMessage)
                    }
                });
            }

            scNotice.show(`Selected translation requests have been successfully deleted`)
            fadeOut(translationRequestsDeleting, 0)
        }

        function updateTranslationRequestRows() {
            if (translationRequestRows.length > 0) {
                translationRequestRows.each(function () {
                    const row = $(this)
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'smartcat_translation_request_info',
                            translationRequestId: $(this).attr('sc-tr-id')
                        },
                        success: function (data) {
                            const isFailedResponse = data.status === 'failed';

                            if (isFailedResponse) {
                                row.addClass('smartcat__error-row')
                            }

                            // project column
                            row.find('.sc-project-name')
                                .find('.sc-loader')
                                .addClass('sc-dn')

                            const projectName = row.find('.sc-project-name')
                                .find('> .sc-value')
                                .removeClass('sc-dn')

                            if (!isFailedResponse) {
                                projectName.find('a')
                                    .find('.value')
                                    .text(data.data.project.name)
                            } else {
                                const smartcatProjectLink = projectName.find('a').attr('href')
                                projectName.html(
                                    `<span style="color:red">Error loading <a href="${smartcatProjectLink}" target="_blank">Smartcat project</a></span>`
                                )
                            }

                            // status column
                            row.find('.sc-tr-status')
                                .find('.sc-loader')
                                .addClass('sc-dn')

                            row.find('.sc-tr-status')
                                .find('.sc-update-tr-progress')
                                .removeClass('sc-dn')

                            let status = ''

                            if (!isFailedResponse) {
                                status = data.data.status.type

                                status += data.data.status.type === 'In progress'
                                    ? ` (${data.data.status.progress}%)` : '';
                            } else {
                                status = '-'
                            }

                            row.find('.sc-tr-status')
                                .find('> .sc-value')
                                .removeClass('sc-dn')
                                .text(status)

                            // deadline column
                            row.find('.sc-project-deadline')
                                .find('.sc-loader')
                                .addClass('sc-dn')
                            row.find('.sc-project-deadline')
                                .find('> .sc-value')
                                .removeClass('sc-dn')
                                .text(!isFailedResponse ? data.data.project.deadline ?? '-' : '-')
                        },
                        error: function () {
                            showNotice('error', defaultErrorMessage)
                        }
                    });
                })
            }
        }

        updateTranslationRequestRows()

        function updateTranslationRequestStatus(translationRequestId) {
            const statusBlock = $(`.sc-tr-status[tr-id="${translationRequestId}"]`);
            statusBlock.find('.sc-loader').removeClass('sc-dn')
            statusBlock.find('.sc-value').addClass('sc-dn')
            statusBlock.find('.sc-update-tr-progress').addClass('sc-dn')

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'smartcat_translation_request_info',
                    translationRequestId: translationRequestId
                },
                success: function (data) {
                    let status = ''
                    status = data.data.status.type
                    status += data.data.status.type === 'In progress'
                        ? ` (${data.data.status.progress}%)` : '';

                    statusBlock.find('.sc-value').html(status)

                    statusBlock.find('.sc-loader').addClass('sc-dn')
                    statusBlock.find('.sc-value').removeClass('sc-dn')
                    statusBlock.find('.sc-update-tr-progress').removeClass('sc-dn')
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                }
            });
        }

        // -- Translation request details

        // Remove post from TR listener
        function removePostFromTR() {
            const popupData = getPopupData();

            const data = {
                action: 'smartcat_remove_post_from_translation_request',
                postId: popupData.postId,
                translationRequestId: popupData.translationRequestId
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    const row = $(`.sc-post-row[sc-post-id="${popupData.postId}"]`)

                    hidePopup()

                    row.addClass('smartcat__error-row')
                    fadeOut(row, 500)
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                    buttonNormalState(getTranslationsButton)
                }
            });
        }

        // -- Smartcat projects --

        function fetchSmatcatProjects(projectName = '') {
            const loader = projectsSelector.find('.sc-dropdown__items__loader')
            projectsLoading = true

            fadeIn(loader)

            const data = {
                action: 'smartcat_fetch_projects',
                projectName: projectName,
                limit: projectsLimit,
                offset: projectsOffset
            };

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function (data) {
                    if (data.data.length === 0) {
                        projectsLoadingIsComplete = true
                    }

                    appendProjectsToSelector(data.data)

                    fadeOut(loader)
                    projectsLoading = false
                },
                error: function () {
                    showNotice('error', defaultErrorMessage)
                    fadeOut(loader)
                    projectsLoading = false
                }
            });

            projectsOffset += projectsLimit
        }

        // -- Settings --

        function makeSecret(length) {
            let result = [];
            let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result.push(characters.charAt(Math.floor(Math.random() *
                    charactersLength)));
            }
            return result.join('');
        }

        $('#generate').click(function () {
            const secret = makeSecret(30);
            $('#api-secret').val(secret);
            $('#api-secret-full').val(secret);
        })

        $('#copy-smartcat-secret').click(function () {
            let $temp = $("<input>");
            $("body").append($temp);
            $temp.val($("#api-secret-full").val()).select();
            document.execCommand("copy");
            $temp.remove();
            alert("Secret key copied to clipboard")
        })

        // helpers

        function hideAllNotices() {
            errorNotice.fadeIn(0, function () {
                errorNotice.fadeOut(0);
            });
            successNotice.fadeIn(0, function () {
                successNotice.fadeOut(0);
            });
            warnNotice.fadeIn(0, function () {
                warnNotice.fadeOut(0);
            });
            infoNotice.fadeIn(0, function () {
                infoNotice.fadeOut(0);
            });
        }

        function showNotice(type = 'info', message = '', hideNotices = false) {
            if (hideNotices) {
                hideAllNotices();
            }
            switch (type) {
                case 'info':
                    infoNotice.html(message);
                    infoNotice.fadeOut(100, function () {
                        infoNotice.fadeIn(100);
                    });
                    break;
                case 'success':
                    successNotice.html(message);
                    successNotice.fadeOut(100, function () {
                        successNotice.fadeIn(100);
                    });
                    break;
                case 'error':
                    errorNotice.html(message);
                    errorNotice.fadeOut(100, function () {
                        errorNotice.fadeIn(100);
                    });
                    break;
                case 'warn':
                    warnNotice.html(message);
                    warnNotice.fadeOut(100, function () {
                        warnNotice.fadeIn(100);
                    });
                    break;
            }
        }

        function getSelectedLanguageCodes() {
            let selectedLanguages = [];
            getSelectedLanguages().each(function () {
                selectedLanguages.push($(this).val());
            });
            return selectedLanguages;
        }

        function getSelectedLanguages() {
            return $('.smartcat-language:checked');
        }

        function getLanguages() {
            return $('.smartcat-language')
        }

        function disable(el) {
            el.prop('disabled', true)
        }

        function disableOff(el) {
            el.prop('disabled', false)
        }

        function buttonNormalState(button) {
            button.prop('disabled', false);
            button.find('.dashicons').show();
            button.find('.dashicons.loader').hide();
        }

        function buttonLoadState(button) {
            button.prop('disabled', true);
            button.find('.dashicons').hide();
            button.find('.dashicons.loader').show();
        }

        function showPopup(message, action, data) {
            popup.find('.smartcat__popup--title').html(message);
            popup.find('.data').text(JSON.stringify(data));
            popup.fadeOut(100, function () {
                popup.fadeIn(100);
            });
            popup.find('.confirm').attr('action', action);
        }

        function hidePopup(e = null) {
            if (e) {
                e.preventDefault()
            }

            popup.fadeIn(100, function () {
                popup.fadeOut(100);
            });

            $(this).prop('disabled', false);
            $('.smartcat__popup--buttons .button').prop('disabled', false);
            $('.smartcat__popup--buttons .dashicons').hide();
        }

        function getPopupData() {
            return JSON.parse(popup.find('.data').text());
        }

        function handlePopupConfirmButton() {
            $(this).prop('disabled', true);
            $(this).find('.dashicons').show();
            switch ($(this).attr('action')) {
                case 'removeLanguage':
                    removeLanguage();
                    break;
                case 'addLanguage':
                    addLanguage();
                    break;
                case 'removeTranslationRequest':
                    removeTranslationRequest();
                    break;
                case 'removePostFromTR':
                    removePostFromTR();
                    break;
            }
        }

        function showMetaboxLoading() {
            fadeIn(
                $('.sc-metabox-loading')
            )
        }

        function hideMetaboxLoading() {
            fadeOut(
                $('.sc-metabox-loading')
            )
        }

        function fadeIn(el, dur = 100) {
            el.fadeOut(dur, function () {
                el.fadeIn(dur);
            });
        }

        function fadeOut(el, dur = 100) {
            el.fadeIn(dur, function () {
                el.fadeOut(dur);
            });
        }

        function getSelectedProject() {
            return projectsSelector.find('> .sc-dropdown__selector')
        }

        function getSelectedProjectId() {
            return getSelectedProject()
                .attr('sc-selected-project-id')
        }

        function selectSmartcatProject(el) {
            const projectId = $(el).attr('sc-project-id')
            const deadline = $(el).attr('sc-deadline')

            let button = projectsSelector
                .find('> .sc-dropdown__selector')

            button.attr('sc-selected-project-id', projectId)
            button.attr('sc-deadline', deadline)

            button.find('.name')
                .text(projectId === 'new' ? 'New project' : $(el).text())
        }

        function appendProjectsToSelector(projects) {
            const list = projectsSelector.find('.sc-dropdown__items--list')

            if (list.html().length === 0) {
                list.append(`
                <button 
                    class="sc-dropdown__items--item" 
                    sc-project-id="new" 
                    sc-deadline=""
                ><b>New project</b></button>
            `)
            }

            for (const project of projects) {
                list.append(`
                <button 
                    class="sc-dropdown__items--item" 
                    sc-project-id="${project.id}" 
                    sc-deadline="${project.deadline}"
                >${project.name}</button>
            `)
            }

            $('.sc-dropdown__items--item').click(function (e) {
                e.preventDefault()
                selectSmartcatProject(this)
            })
        }

        function clearProjectsToSelector() {
            console.log(projectsSelector
                .find('.sc-dropdown__items--list'));
            projectsSelector
                .find('.sc-dropdown__items--list')
                .html('')
        }

        function findProjectsByName() {
            if (!projectsLoading) {
                projectsOffset = 0
                projectsLoadingIsComplete = false

                clearProjectsToSelector()

                fetchSmatcatProjects(
                    getProjectsSelectorInputVal()
                )
            }
        }

        function getProjectsSelectorInputVal() {
            return projectsSelector.find('.sc-dropdown__search').val()
        }
    }
)

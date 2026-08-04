document.addEventListener('DOMContentLoaded', function () {
    const keyInput = document.getElementById('key');
    const keyPreview = document.getElementById('key-preview');
    const normalizeButton = document.getElementById('normalize-key-btn');
    const valueInputs = document.querySelectorAll('.js-translation-value');
    const openTranslateModalButton = document.getElementById('open-form-google-translate-modal');
    const formTranslateModalElement = document.getElementById('formGoogleTranslateModal');
    const formTranslateSource = document.getElementById('form-translate-source');
    const formTranslateTarget = document.getElementById('form-translate-target');
    const formTranslateMode = document.getElementById('form-translate-mode');
    const runTranslateButton = document.getElementById('run-form-google-translate');

    let formTranslateModal = null;
    let translateButtonDefaultHtml = '';

    if (formTranslateModalElement && window.bootstrap) {
        formTranslateModal = new bootstrap.Modal(formTranslateModalElement);
    }

    if (runTranslateButton) {
        translateButtonDefaultHtml = runTranslateButton.innerHTML;
    }

    const updateKeyPreview = () => {
        if (!keyPreview || !keyInput) {
            return;
        }

        const value = keyInput.value.trim();
        keyPreview.textContent = value !== '' ? value : '—';
    };

    const normalizeSentenceKey = (value) => {
        return String(value || '')
            .replace(/\r\n/g, ' ')
            .replace(/\n/g, ' ')
            .replace(/\s+/g, ' ')
            .replace(/\s+([:;,.!?])/g, '$1')
            .replace(/([:;,.!?])([^\s])/g, '$1 $2')
            .trim();
    };

    const updateTabBadges = () => {
        valueInputs.forEach((input) => {
            const pane = input.closest('.tab-pane');
            const paneId = pane ? pane.id : null;
            const tabButton = paneId ? document.querySelector('[data-bs-target="#' + paneId + '"]') : null;
            const badge = tabButton ? tabButton.querySelector('.badge') : null;

            if (!badge) {
                return;
            }

            if (input.value.trim() !== '') {
                badge.className = 'ms-1 badge bg-success-subtle text-success';
                badge.textContent = 'Done';
            } else {
                badge.className = 'ms-1 badge bg-light text-body';
                badge.textContent = 'Draft';
            }
        });
    };

    const findTextareaByLocale = (locale) => {
        return document.querySelector('.js-translation-value[data-locale="' + locale + '"]');
    };

    const getKeyFallbackText = () => {
        if (!keyInput) {
            return '';
        }

        return normalizeSentenceKey(keyInput.value);
    };

    const getSourceText = (sourceLocale) => {
        const sourceTextarea = findTextareaByLocale(sourceLocale);
        const sourceValue = sourceTextarea ? sourceTextarea.value.trim() : '';

        if (sourceValue !== '') {
            return sourceValue;
        }

        return getKeyFallbackText();
    };

    const setTranslateButtonLoading = () => {
        if (!runTranslateButton) {
            return;
        }

        runTranslateButton.disabled = true;
        runTranslateButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Translating';
    };

    const resetTranslateButton = () => {
        if (!runTranslateButton) {
            return;
        }

        runTranslateButton.disabled = false;
        runTranslateButton.innerHTML = translateButtonDefaultHtml;
    };

    const activateTargetTab = (targetTextarea) => {
        const targetPane = targetTextarea ? targetTextarea.closest('.tab-pane') : null;
        const targetPaneId = targetPane ? targetPane.id : null;
        const targetTabButton = targetPaneId ? document.querySelector('[data-bs-target="#' + targetPaneId + '"]') : null;

        if (targetTabButton) {
            targetTabButton.click();
        }
    };

    const applyTranslatedValue = (targetTextarea, translated, mode) => {
        targetTextarea.value = translated;
        targetTextarea.dispatchEvent(new Event('input', { bubbles: true }));

        if (mode === 'single') {
            activateTargetTab(targetTextarea);
        }

        if (formTranslateModal) {
            formTranslateModal.hide();
        }
    };

    const parseGoogleResponse = (data) => {
        if (!Array.isArray(data) || !Array.isArray(data[0])) {
            throw new Error('Google translate response is invalid.');
        }

        return data[0]
            .map((item) => Array.isArray(item) ? item[0] : '')
            .join('')
            .trim();
    };

    const translateTextWithGoogle = async (text, source, target) => {
        const normalizedText = String(text || '').trim();

        if (normalizedText === '') {
            return '';
        }

        if (source === target) {
            return normalizedText;
        }

        const params = new URLSearchParams({
            client: 'gtx',
            sl: source,
            tl: target,
            dt: 't',
            q: normalizedText,
        });

        const response = await fetch('https://translate.googleapis.com/translate_a/single?' + params.toString());

        if (!response.ok) {
            throw new Error('Google translate request failed.');
        }

        const data = await response.json();
        return parseGoogleResponse(data);
    };

    if (normalizeButton && keyInput) {
        normalizeButton.addEventListener('click', function () {
            keyInput.value = normalizeSentenceKey(keyInput.value);
            updateKeyPreview();
            keyInput.focus();
        });
    }

    if (keyInput) {
        keyInput.addEventListener('input', updateKeyPreview);
    }

    valueInputs.forEach((input) => {
        input.addEventListener('input', updateTabBadges);
    });

    if (openTranslateModalButton && formTranslateModal) {
        openTranslateModalButton.addEventListener('click', function () {
            if (formTranslateMode) {
                formTranslateMode.value = 'bulk';
            }

            resetTranslateButton();
            formTranslateModal.show();
        });
    }

    document.querySelectorAll('.js-translate-single-language').forEach((button) => {
        button.addEventListener('click', function () {
            const targetLocale = this.dataset.target || '';

            if (formTranslateTarget) {
                formTranslateTarget.value = targetLocale;
            }

            if (formTranslateMode) {
                formTranslateMode.value = 'single';
            }

            resetTranslateButton();

            if (formTranslateModal) {
                formTranslateModal.show();
            }
        });
    });

    if (runTranslateButton) {
        runTranslateButton.addEventListener('click', async function () {
            const source = formTranslateSource ? String(formTranslateSource.value || '').trim() : '';
            const target = formTranslateTarget ? String(formTranslateTarget.value || '').trim() : '';
            const mode = formTranslateMode ? String(formTranslateMode.value || 'single') : 'single';

            if (source === '' || target === '') {
                window.alert('Please select source and target languages.');
                resetTranslateButton();
                return;
            }

            const targetTextarea = findTextareaByLocale(target);

            if (!targetTextarea) {
                window.alert('Selected target language field was not found.');
                resetTranslateButton();
                return;
            }

            const sourceText = getSourceText(source);

            if (sourceText === '') {
                window.alert('Source language value and translation key are empty.');
                resetTranslateButton();
                return;
            }

            setTranslateButtonLoading();

            try {
                const translated = await translateTextWithGoogle(sourceText, source, target);

                if (translated === '') {
                    throw new Error('Empty translation response.');
                }

                applyTranslatedValue(targetTextarea, translated, mode);
            } catch (error) {
                window.alert(error.message || 'Translation failed.');
            } finally {
                resetTranslateButton();
            }
        });
    }

    if (formTranslateModalElement) {
        formTranslateModalElement.addEventListener('hidden.bs.modal', function () {
            resetTranslateButton();
        });
    }

    updateKeyPreview();
    updateTabBadges();
});

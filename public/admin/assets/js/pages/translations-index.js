document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const languageSelect = document.getElementById('language_select');
    const exportButton = document.getElementById('btn-export');
    const importForm = document.getElementById('translations-import-form');
    const googleTranslateForm = document.getElementById('google-translate-form');
    const operationModalElement = document.getElementById('operationModal');
    const googleTranslateModalElement = document.getElementById('googleTranslateModal');
    const openGoogleTranslateModalButton = document.getElementById('btn-open-google-translate-modal');

    let operationModal = null;
    let googleTranslateModal = null;
    let visualPercent = 0;
    let targetPercent = 0;
    let visualTimer = null;

    if (operationModalElement && window.bootstrap) {
        operationModal = new bootstrap.Modal(operationModalElement, {
            backdrop: 'static',
            keyboard: false,
        });
    }

    if (googleTranslateModalElement && window.bootstrap) {
        googleTranslateModal = new bootstrap.Modal(googleTranslateModalElement);
    }

    const bar = document.getElementById('opBar');
    const pct = document.getElementById('opPct');
    const hint = document.getElementById('opHint');
    const titleElement = document.getElementById('opTitle');
    const subtitleElement = document.getElementById('opSub');

    const renderProgress = (value) => {
        const safeValue = Math.max(0, Math.min(100, Math.round(value)));

        if (bar) {
            bar.style.width = `${safeValue}%`;
            bar.setAttribute('aria-valuenow', String(safeValue));
        }

        if (pct) {
            pct.textContent = String(safeValue);
        }
    };

    const animateProgress = () => {
        if (visualTimer) {
            clearInterval(visualTimer);
        }

        visualTimer = window.setInterval(() => {
            if (visualPercent >= targetPercent) {
                clearInterval(visualTimer);
                visualTimer = null;
                return;
            }

            const remaining = targetPercent - visualPercent;
            const step = remaining > 20 ? 3 : remaining > 10 ? 2 : 1;

            visualPercent += step;
            renderProgress(visualPercent);
        }, 40);
    };

    const setProgressState = (percent, message) => {
        targetPercent = Math.max(0, Math.min(100, Number(percent || 0)));

        if (hint) {
            hint.textContent = message || '';
        }

        animateProgress();
    };

    const showProgress = (title, subtitle) => {
        visualPercent = 0;
        targetPercent = 0;
        renderProgress(0);

        if (titleElement) {
            titleElement.textContent = title;
        }

        if (subtitleElement) {
            subtitleElement.textContent = subtitle;
        }

        if (hint) {
            hint.textContent = 'Preparing...';
        }

        if (operationModal) {
            operationModal.show();
        }
    };

    const hideProgress = () => {
        if (visualTimer) {
            clearInterval(visualTimer);
            visualTimer = null;
        }

        if (operationModal) {
            operationModal.hide();
        }
    };

    const parseJsonSafe = async (response) => {
        const text = await response.text();
        const trimmed = text.trim();

        if (trimmed === '') {
            throw new Error('Empty response from server.');
        }

        try {
            return JSON.parse(trimmed);
        } catch (error) {
            const firstBraceIndex = trimmed.indexOf('{');
            const lastBraceIndex = trimmed.lastIndexOf('}');

            if (firstBraceIndex !== -1 && lastBraceIndex !== -1 && lastBraceIndex > firstBraceIndex) {
                const possibleJson = trimmed.slice(firstBraceIndex, lastBraceIndex + 1);

                try {
                    return JSON.parse(possibleJson);
                } catch (innerError) {
                    throw new Error('Server returned invalid JSON response.');
                }
            }

            throw new Error('Server returned invalid JSON response.');
        }
    };

    const startTrackedOperation = async (element, payload, options = {}) => {
        const startUrl = element.dataset.startUrl;
        const progressUrlTemplate = element.dataset.progressUrl;

        if (!startUrl || !progressUrlTemplate) {
            return;
        }

        showProgress(
            options.title || 'Processing',
            options.subtitle || 'Please keep this page open.'
        );

        try {
            const response = await fetch(startUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: payload,
            });

            const result = await parseJsonSafe(response);

            if (!response.ok || !result.token) {
                throw new Error(result.message || 'Operation failed.');
            }

            const progressUrl = progressUrlTemplate.replace('___TOKEN___', result.token);

            const poll = async () => {
                const progressResponse = await fetch(progressUrl, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const progress = await parseJsonSafe(progressResponse);

                setProgressState(progress.percent || 0, progress.message || '');

                if (progress.done) {
                    targetPercent = 100;
                    animateProgress();

                    window.setTimeout(() => {
                        if (progress.reload) {
                            window.location.reload();
                            return;
                        }

                        hideProgress();

                        if (progress.ok === false) {
                            window.alert(progress.message || 'Operation failed.');
                        }
                    }, 500);

                    return;
                }

                window.setTimeout(poll, 1200);
            };

            poll();
        } catch (error) {
            hideProgress();
            window.alert(error.message || 'Operation failed.');
        }
    };

    if (languageSelect && exportButton) {
        languageSelect.addEventListener('change', function () {
            const exportUrlTemplate = exportButton.dataset.exportUrl || '';
            if (exportUrlTemplate !== '') {
                exportButton.href = exportUrlTemplate.replace('___LOCALE___', this.value);
            }
        });
    }

    const syncButton = document.getElementById('btn-sync');
    if (syncButton) {
        syncButton.addEventListener('click', function () {
            startTrackedOperation(this, null, {
                title: 'Syncing keys',
                subtitle: 'Scanning project files and syncing translations.',
            });
        });
    }

    if (openGoogleTranslateModalButton && googleTranslateModal) {
        openGoogleTranslateModalButton.addEventListener('click', function () {
            googleTranslateModal.show();
        });
    }

    if (googleTranslateForm) {
        googleTranslateForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const submitButton = googleTranslateForm.querySelector('button[type="submit"]');
            const defaultButtonHtml = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Starting';
            }

            try {
                const formData = new FormData(googleTranslateForm);
                const source = String(formData.get('source') || '').trim();
                const target = String(formData.get('target') || '').trim();

                if (!source || !target) {
                    throw new Error('Please select source and target languages.');
                }

                if (googleTranslateModal) {
                    googleTranslateModal.hide();
                }

                startTrackedOperation(googleTranslateForm, formData, {
                    title: 'Google translating',
                    subtitle: 'Missing translations are being translated with Google.',
                });
            } catch (error) {
                window.alert(error.message || 'Operation failed.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = defaultButtonHtml;
                }
            }
        });
    }

    if (importForm) {
        importForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(importForm);

            startTrackedOperation(importForm, formData, {
                title: 'Importing',
                subtitle: 'Excel file is being processed.',
            });
        });
    }
});

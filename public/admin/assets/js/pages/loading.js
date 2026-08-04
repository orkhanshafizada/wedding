/* Global submit button loading handler for Admin
 * - Applies to all forms across admin (no per-page changes required)
 * - Shows Bootstrap spinner and prevents double submission
 * - Respects per-button overrides via data-loading-text
 * - Opt-out any button with data-no-loading="true"
 * - Restores state on bfcache (Back/Forward navigation)
 */
(function () {
    'use strict';

    // Expose minimal API for optional manual control on AJAX flows
    var AdminLoading = {
        set: setLoading,
        reset: resetLoading
    };
    window.AdminLoading = AdminLoading;

    // Attach once, capture submitter reliably
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        // Find the actual submitter (modern browsers), fallback to the first submit button
        var submitter = (typeof e.submitter !== 'undefined' && e.submitter) ||
            form.querySelector('button[type="submit"]:not([disabled]):not([data-no-loading]), input[type="submit"]:not([disabled]):not([data-no-loading])');

        if (!submitter || submitter.hasAttribute('data-no-loading')) return;

        // Set loading only on the clicked submitter
        setLoading(submitter);

        // Optionally disable other submit buttons to prevent double submit
        var others = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        others.forEach(function (btn) {
            if (btn !== submitter) {
                btn.disabled = true;
                btn.setAttribute('aria-disabled', 'true');
            }
        });
    }, true); // capture to ensure we catch the original submit

    // Restore on page show (e.g., user navigates back using bfcache)
    window.addEventListener('pageshow', function () {
        var loadingButtons = document.querySelectorAll('.is-loading[data-original-html]');
        loadingButtons.forEach(resetLoading);
    });

    /**
     * Put a button into loading state with spinner and text.
     * @param {HTMLElement} btn
     */
    function setLoading(btn) {
        if (!btn || btn.disabled) return;

        // Preserve original content and width to avoid layout shift
        if (!btn.dataset.originalHtml) {
            btn.dataset.originalHtml = btn.innerHTML;
        }
        if (!btn.dataset.originalWidth) {
            var rect = btn.getBoundingClientRect();
            btn.style.width = rect.width ? rect.width + 'px' : btn.style.width;
            btn.dataset.originalWidth = btn.style.width || '';
        }

        var loadingText = btn.getAttribute('data-loading-text');
        if (!loadingText) {
            var currentText = (btn.textContent || '').trim();
            loadingText = currentText ? (currentText + '...') : 'Loading...';
        }

        // Bootstrap 5 spinner
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + loadingText;

        // Accessibility + prevent double click
        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
        btn.classList.add('is-loading');
    }

    /**
     * Restore a button from loading state back to original.
     * @param {HTMLElement} btn
     */
    function resetLoading(btn) {
        if (!btn) return;

        // Restore original HTML and width if stored
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
            delete btn.dataset.originalHtml;
        }
        if (btn.dataset.originalWidth !== undefined) {
            btn.style.width = btn.dataset.originalWidth || '';
            delete btn.dataset.originalWidth;
        }

        btn.disabled = false;
        btn.removeAttribute('aria-disabled');
        btn.classList.remove('is-loading');
    }
})();

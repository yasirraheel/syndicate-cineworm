(function (window, document) {
    'use strict';

    var activeDialog = null;

    function ensureRoot() {
        var root = document.getElementById('syndicate-dialog-root');
        if (root) return root;

        root = document.createElement('div');
        root.id = 'syndicate-dialog-root';
        root.hidden = true;
        document.body.appendChild(root);
        return root;
    }

    function iconMarkup(variant) {
        if (variant === 'danger') {
            return '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.8 20h18.4L12 3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 9v5M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
        }

        return '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 10v6M12 7h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    }

    function openDialog(message, options) {
        options = options || {};
        if (activeDialog) activeDialog.finish(false);

        var root = ensureRoot();
        var variant = options.variant === 'danger' ? 'danger' : 'primary';
        var isAlert = options.mode === 'alert';
        var backdrop = document.createElement('div');
        backdrop.className = 'syndicate-dialog-backdrop';
        backdrop.innerHTML = '<div class="syndicate-dialog-card" role="dialog" aria-modal="true" aria-labelledby="syndicate-dialog-title">'
            + '<div class="syndicate-dialog-accent"></div>'
            + '<div class="syndicate-dialog-body">'
            + '<div class="syndicate-dialog-icon '+variant+'">'+iconMarkup(variant)+'</div>'
            + '<h2 class="syndicate-dialog-title" id="syndicate-dialog-title"></h2>'
            + '<p class="syndicate-dialog-message"></p>'
            + '<div class="syndicate-dialog-actions">'
            + (isAlert ? '' : '<button type="button" class="syndicate-dialog-button cancel" data-dialog-cancel>Cancel</button>')
            + '<button type="button" class="syndicate-dialog-button '+variant+'" data-dialog-confirm></button>'
            + '</div></div></div>';

        var title = backdrop.querySelector('.syndicate-dialog-title');
        var text = backdrop.querySelector('.syndicate-dialog-message');
        var confirmButton = backdrop.querySelector('[data-dialog-confirm]');
        var cancelButton = backdrop.querySelector('[data-dialog-cancel]');
        title.textContent = options.title || (isAlert ? 'Notice' : 'Confirm action');
        text.textContent = String(message || 'Please confirm this action.');
        confirmButton.textContent = options.confirmLabel || (isAlert ? 'OK' : 'Confirm');
        root.replaceChildren(backdrop);
        root.hidden = false;

        var finished = false;
        var finish = function (result) {
            if (finished) return;
            finished = true;
            document.removeEventListener('keydown', onKeyDown, true);
            root.hidden = true;
            root.replaceChildren();
            activeDialog = null;
            resolve(result);
        };
        var onKeyDown = function (event) {
            if (event.key === 'Escape' && !isAlert) finish(false);
            if (event.key === 'Enter') finish(true);
        };
        var resolve;
        var promise = new Promise(function (done) { resolve = done; });
        activeDialog = { finish: finish };
        confirmButton.addEventListener('click', function () { finish(true); });
        if (cancelButton) cancelButton.addEventListener('click', function () { finish(false); });
        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop && !isAlert) finish(false);
        });
        document.addEventListener('keydown', onKeyDown, true);
        window.setTimeout(function () { confirmButton.focus(); }, 0);
        return promise;
    }

    window.SyndicateDialog = {
        alert: function (message, options) {
            options = Object.assign({}, options || {}, { mode: 'alert' });
            return openDialog(message, options);
        },
        confirm: function (message, options) {
            return openDialog(message, options || {});
        }
    };

    // Existing alert() calls also use the same themed dialog.
    window.alert = function (message) {
        return window.SyndicateDialog.alert(message);
    };

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        var message = form.getAttribute('data-confirm');
        if (!message && form.id !== 'leave-subscription') return;
        if (form.dataset.syndicateDialogConfirmed === '1') {
            delete form.dataset.syndicateDialogConfirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        window.SyndicateDialog.confirm(message || 'Are you sure you want to continue?', {
            variant: form.getAttribute('data-dialog-variant') || 'primary',
            confirmLabel: form.getAttribute('data-dialog-confirm-label') || 'Continue'
        }).then(function (confirmed) {
            if (!confirmed) return;
            form.dataset.syndicateDialogConfirmed = '1';
            HTMLFormElement.prototype.submit.call(form);
        });
    }, true);
}(window, document));

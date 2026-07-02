document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('[data-sidebar-shell]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!shell || !toggle) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 980px)');

    if (mobileQuery.matches) {
        shell.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', () => {
        shell.classList.toggle('sidebar-collapsed');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-confirm-modal]');
    const message = document.querySelector('[data-confirm-message]');
    const cancel = document.querySelector('[data-confirm-cancel]');
    const ok = document.querySelector('[data-confirm-ok]');
    let pendingForm = null;

    if (!modal || !message || !cancel || !ok) {
        return;
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            pendingForm = form;
            message.textContent = form.dataset.confirm || 'Please confirm this action.';
            modal.hidden = false;
        });
    });

    cancel.addEventListener('click', () => {
        pendingForm = null;
        modal.hidden = true;
    });

    ok.addEventListener('click', () => {
        if (pendingForm) {
            pendingForm.submit();
        }
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            pendingForm = null;
            modal.hidden = true;
        }
    });
});

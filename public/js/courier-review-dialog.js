(() => {
    const dismissedPrompts = window.courierReviewDismissedPrompts
        ?? (window.courierReviewDismissedPrompts = new Set());

    const initializeDialogs = () => {
        const orders = document.querySelector('[data-live-fragment="customer-orders"]');
        if (!orders) {
            window.courierReviewDialogObserver?.disconnect();
            return;
        }

        window.courierReviewDialogObserver?.disconnect();
        orders.querySelectorAll('[data-courier-review-dialog]').forEach((dialog) => {
            if (dialog.dataset.dismissHandlerInstalled) {
                return;
            }

            dialog.dataset.dismissHandlerInstalled = 'true';
            dialog.addEventListener('close', () => {
                dialog.dataset.promptDismissed = 'true';
                dismissedPrompts.add(dialog.dataset.orderId);
            });
        });

        const alreadyOpen = orders.querySelector('[data-courier-review-dialog][open]');
        if (!alreadyOpen) {
            const prompt = [...orders.querySelectorAll('[data-courier-review-dialog][data-auto-prompt]')]
                .find((dialog) => !dialog.dataset.promptDismissed
                    && !dismissedPrompts.has(dialog.dataset.orderId));

            if (prompt) {
                prompt.showModal();
            }
        }

        window.courierReviewDialogObserver = new MutationObserver(initializeDialogs);
        window.courierReviewDialogObserver.observe(orders, { childList: true, subtree: true });
    };

    if (!window.courierReviewDialogInstalled) {
        window.courierReviewDialogInstalled = true;
        document.addEventListener('click', (event) => {
            const openButton = event.target.closest('[data-open-courier-review]');
            if (openButton) {
                const dialogId = openButton.getAttribute('aria-controls');
                const dialog = dialogId ? document.getElementById(dialogId) : null;
                if (dialog && !dialog.open) {
                    dialog.showModal();
                }
                return;
            }

            const closeButton = event.target.closest('[data-close-courier-review]');
            if (closeButton) {
                const dialog = closeButton.closest('dialog');
                if (dialog) {
                    dialog.dataset.promptDismissed = 'true';
                    dismissedPrompts.add(dialog.dataset.orderId);
                    dialog.close();
                }
            }
        });

        document.addEventListener('app:page-loaded', initializeDialogs);
    }

    initializeDialogs();
})();

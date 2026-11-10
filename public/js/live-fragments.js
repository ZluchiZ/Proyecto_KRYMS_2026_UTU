(() => {
    if (window.liveFragmentsInstalled) {
        return;
    }

    window.liveFragmentsInstalled = true;
    const interval = 4000;
    let requestInProgress = false;
    let courierCountRequestInProgress = false;

    document.addEventListener('input', (event) => {
        const form = event.target.closest('form');
        if (form && event.target.matches('input:not([type="hidden"]), textarea, select')) {
            form.dataset.liveDirty = 'true';
        }
    });

    document.addEventListener('change', (event) => {
        const form = event.target.closest('form');
        if (form && event.target.matches('input:not([type="hidden"]), textarea, select')) {
            form.dataset.liveDirty = 'true';
        }
    });

    document.addEventListener('submit', (event) => {
        event.target.removeAttribute('data-live-dirty');
    });

    const refreshCourierCount = async () => {
        const fragment = document.querySelector('[data-live-url][data-courier-count]');
        if (!fragment || document.visibilityState !== 'visible' || courierCountRequestInProgress) {
            return;
        }

        courierCountRequestInProgress = true;

        try {
            const response = await fetch(fragment.dataset.liveUrl, {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok || response.redirected) {
                throw new Error(`La disponibilidad respondió con HTTP ${response.status}.`);
            }

            const data = await response.json();
            if (!Number.isInteger(data.count) || data.count < 0) {
                throw new Error('La cantidad de repartidores recibida no es válida.');
            }

            fragment.querySelector('[data-courier-count]').textContent = String(data.count);
            fragment.querySelector('[data-courier-label]').textContent =
                `${data.count === 1 ? 'repartidor conectado' : 'repartidores conectados'} ahora.`;
        } catch (error) {
            console.error('No se pudo actualizar la cantidad de repartidores.', error);
        } finally {
            courierCountRequestInProgress = false;
        }
    };

    const refreshFragments = async () => {
        if (document.visibilityState !== 'visible' || requestInProgress) {
            return;
        }

        const fragments = document.querySelectorAll('[data-live-fragment]');
        if (fragments.length === 0) {
            window.setTimeout(refreshFragments, interval);
            return;
        }

        requestInProgress = true;

        try {
            const response = await fetch(window.location.href, {
                cache: 'no-store',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok || response.redirected) {
                throw new Error(`La actualización en vivo respondió con HTTP ${response.status}.`);
            }

            const html = await response.text();
            const updatedDocument = new DOMParser().parseFromString(html, 'text/html');

            for (const fragment of fragments) {
                if (fragment.dataset.liveUrl) {
                    continue;
                }

                const name = fragment.dataset.liveFragment;
                const updatedFragment = updatedDocument.querySelector(
                    `[data-live-fragment="${CSS.escape(name)}"]`,
                );

                if (!updatedFragment) {
                    throw new Error(`No se encontró el fragmento actualizado "${name}".`);
                }

                if (fragment.innerHTML === updatedFragment.innerHTML
                    || fragment.contains(document.activeElement)
                    || fragment.querySelector('form[data-live-dirty="true"]')) {
                    continue;
                }

                fragment.innerHTML = updatedFragment.innerHTML;
            }
        } catch (error) {
            console.error('No se pudo actualizar la página en vivo.', error);
        } finally {
            requestInProgress = false;
            window.setTimeout(refreshFragments, interval);
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            refreshFragments();
            refreshCourierCount();
        }
    });

    window.setInterval(refreshCourierCount, interval);
    window.setTimeout(refreshFragments, interval);
})();

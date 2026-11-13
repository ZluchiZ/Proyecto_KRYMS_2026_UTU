(() => {
    if (window.fetchNavigationInstalled) return;
    window.fetchNavigationInstalled = true;

    const navigationScript = document.querySelector('script[data-fetch-navigation]');
    const navigationScriptUrl = navigationScript?.src;
    const progress = document.createElement('div');
    const feedback = document.createElement('div');
    let activeRequests = 0;
    let progressTimer;
    let searchTimer;
    let latestRequestId = 0;
    let activeSearchController;
    let latestSearchId = 0;

    progress.setAttribute('aria-hidden', 'true');
    progress.style.cssText = 'position:fixed;inset:0 100% auto 0;height:3px;background:var(--palette-orange,#f76e11);z-index:2147483647;transition:inset .2s ease';
    feedback.setAttribute('role', 'status');
    feedback.setAttribute('aria-live', 'polite');
    feedback.style.cssText = 'position:fixed;right:1rem;bottom:1rem;z-index:2147483647;max-width:min(28rem,calc(100vw - 2rem));padding:.75rem 1rem;border-radius:.5rem;background:var(--palette-coral,#fc4f4f);color:#321806;font:500 14px/1.4 system-ui,sans-serif;box-shadow:0 4px 16px #0003';
    feedback.hidden = true;
    document.body.append(progress, feedback);

    const startRequest = () => {
        activeRequests += 1;
        clearTimeout(progressTimer);
        progressTimer = setTimeout(() => {
            progress.style.inset = '0 35% auto 0';
        }, 120);
        feedback.hidden = true;
    };

    const finishRequest = () => {
        activeRequests = Math.max(0, activeRequests - 1);
        if (activeRequests === 0) {
            clearTimeout(progressTimer);
            progress.style.inset = '0 0 auto 100%';
            setTimeout(() => {
                progress.style.inset = '0 100% auto 0';
            }, 220);
        }
    };

    const showError = (message) => {
        feedback.textContent = message;
        feedback.hidden = false;
        setTimeout(() => {
            feedback.hidden = true;
        }, 6000);
    };

    const executePageScripts = async () => {
        for (const script of document.querySelectorAll('script')) {
            if (script.src && navigationScriptUrl && script.src === navigationScriptUrl) {
                script.type = 'application/x-fetch-navigation';
                continue;
            }

            const replacement = document.createElement('script');
            for (const attribute of script.attributes) {
                replacement.setAttribute(attribute.name, attribute.value);
            }
            const scriptType = script.type || 'text/javascript';
            replacement.textContent = !script.src
                && ['text/javascript', 'application/javascript'].includes(scriptType)
                ? `(() => {\n${script.textContent}\n})();`
                : script.textContent;

            if (replacement.src) {
                replacement.async = false;
                await new Promise((resolve) => {
                    replacement.addEventListener('load', resolve, { once: true });
                    replacement.addEventListener('error', resolve, { once: true });
                    script.replaceWith(replacement);
                });
            } else {
                script.replaceWith(replacement);
            }
        }
    };

    const renderResponse = async (response, requestedUrl, historyMode) => {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('text/html')) {
            throw new Error('El servidor devolvió una respuesta que no se puede mostrar.');
        }

        const html = await response.text();
        const nextDocument = new DOMParser().parseFromString(html, 'text/html');
        if (!nextDocument.body || !nextDocument.documentElement) {
            throw new Error('No se pudo actualizar la pantalla con la respuesta recibida.');
        }

        const resolvedUrl = response.url || requestedUrl.href;
        const finalUrl = new URL(resolvedUrl, window.location.href);
        if (!finalUrl.hash) finalUrl.hash = requestedUrl.hash;
        const samePage = finalUrl.pathname === window.location.pathname;
        const scrollPosition = window.scrollY;

        document.documentElement.lang = nextDocument.documentElement.lang || 'es';
        document.title = nextDocument.title;
        document.head.replaceChildren(...Array.from(nextDocument.head.childNodes, (node) => node.cloneNode(true)));
        document.body.replaceWith(nextDocument.body.cloneNode(true));
        document.body.append(progress, feedback);

        if (historyMode === 'push') {
            history.pushState({ fetchNavigation: true }, '', finalUrl.href);
        } else if (historyMode === 'replace') {
            history.replaceState({ fetchNavigation: true }, '', finalUrl.href);
        }

        await executePageScripts();
        document.dispatchEvent(new Event('app:page-loaded'));
        const anchorId = decodeURIComponent(finalUrl.hash.slice(1));
        const anchor = anchorId ? document.getElementById(anchorId) : null;
        if (anchor) anchor.scrollIntoView();
        else window.scrollTo(0, samePage ? scrollPosition : 0);
    };

    const requestPage = async (url, options = {}, historyMode = 'push') => {
        const requestedUrl = new URL(url, window.location.href);
        const requestId = ++latestRequestId;
        startRequest();

        try {
            const response = await fetch(requestedUrl, {
                ...options,
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
            });
            if (requestId !== latestRequestId) return;
            await renderResponse(response, requestedUrl, historyMode);
        } catch (error) {
            showError(error instanceof Error ? error.message : 'No se pudo completar la acción. Intentá nuevamente.');
        } finally {
            finishRequest();
        }
    };

    const isEligibleForm = (form) => {
        const hasNativeSubmit = form.matches('[data-native-submit], [data-ajax="false"]');
        if (hasNativeSubmit || form.target && form.target !== '_self') {
            return false;
        }

        if (form.enctype && form.enctype.toLowerCase().includes('multipart/form-data')) {
            return false;
        }

        const hasFileInput = form.querySelector('input[type="file"], input[name="imagen"], input[name="logo"]');
        if (hasFileInput) {
            return false;
        }

        const action = new URL(form.action || window.location.href, window.location.href);
        return action.origin === window.location.origin;
    };

    const updateLiveSearch = async (form, { updateUrl = true } = {}) => {
        const input = form.querySelector('input[name="q"]');
        const resultsContainer = document.getElementById('search-results-container');
        const resultsUrl = form.dataset.resultsUrl;
        if (!input || !resultsContainer || !resultsUrl) return;

        const minPrice = form.elements.namedItem('min_price');
        const maxPrice = form.elements.namedItem('max_price');
        if (minPrice instanceof HTMLInputElement && maxPrice instanceof HTMLInputElement) {
            maxPrice.setCustomValidity(
                minPrice.value !== '' && maxPrice.value !== '' && Number(maxPrice.value) < Number(minPrice.value)
                    ? 'El precio máximo debe ser igual o mayor al precio mínimo.'
                    : '',
            );
            if (!form.reportValidity()) return;
        }

        const query = input.value.trim();
        const formData = new FormData(form);
        const searchParams = new URLSearchParams();
        for (const [name, value] of formData.entries()) {
            if (typeof value === 'string' && value.trim() !== '') searchParams.append(name, value);
        }
        const hasFilters = Array.from(form.querySelectorAll('[data-live-search-filter]'))
            .some((control) => control.type === 'checkbox' ? control.checked : control.value.trim() !== '');
        const searchId = ++latestSearchId;
        activeSearchController?.abort();

        if (!query && !hasFilters) {
            if (document.getElementById('category-search-results') || form.dataset.categorySearchActive === 'true') {
                const url = new URL(form.action, window.location.href);
                for (const key of ['q', 'categoria', 'min_price', 'max_price', 'offers', 'available']) {
                    url.searchParams.delete(key);
                }
                requestPage(url, { method: 'GET' }, 'replace');
                return;
            }
            resultsContainer.replaceChildren();
            if (updateUrl) {
                const url = new URL(form.action, window.location.href);
                for (const key of ['q', 'categoria', 'min_price', 'max_price', 'offers', 'available']) {
                    url.searchParams.delete(key);
                }
                history.replaceState(history.state, '', url);
            }
            return;
        }

        const url = new URL(resultsUrl, window.location.href);
        url.search = searchParams.toString();
        activeSearchController = new AbortController();
        resultsContainer.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeSearchController.signal,
            });
            if (!response.ok) throw new Error('No se pudieron cargar los resultados de búsqueda.');
            const html = await response.text();
            if (searchId !== latestSearchId) return;

            const parsedResults = new DOMParser().parseFromString(html, 'text/html');
            const results = parsedResults.querySelector('#search-results');
            if (!results) throw new Error('La respuesta de búsqueda no contiene resultados válidos.');

            const categoryResults = document.getElementById('category-search-results');
            if (categoryResults) {
                form.dataset.categorySearchActive = 'true';
                categoryResults.replaceWith(results);
            } else {
                resultsContainer.replaceChildren(results);
            }
            if (updateUrl) {
                const currentUrl = new URL(form.action, window.location.href);
                for (const key of ['q', 'categoria', 'min_price', 'max_price', 'offers', 'available']) {
                    currentUrl.searchParams.delete(key);
                }
                for (const [name, value] of searchParams.entries()) {
                    currentUrl.searchParams.append(name, value);
                }
                history.replaceState(history.state, '', currentUrl);
            }
            document.dispatchEvent(new Event('app:search-results-updated'));
        } catch (error) {
            if (error.name !== 'AbortError') {
                showError(error instanceof Error ? error.message : 'No se pudieron cargar los resultados.');
            }
        } finally {
            if (searchId === latestSearchId) resultsContainer.removeAttribute('aria-busy');
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const hasFileInput = Array.from(form.elements).some((element) => element instanceof HTMLInputElement && element.type === 'file');
        if (hasFileInput || form.enctype?.toLowerCase().includes('multipart/form-data') || form.matches('[data-native-submit], [data-ajax="false"]')) {
            return;
        }

        if (!isEligibleForm(form)) return;
        if (event.defaultPrevented) return;
        if (form.matches('[data-live-search]')) {
            event.preventDefault();
            clearTimeout(searchTimer);
            updateLiveSearch(form);
            return;
        }
        clearTimeout(searchTimer);
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        const method = (form.method || 'GET').toUpperCase();
        const action = new URL(form.action || window.location.href, window.location.href);
        const formData = event.submitter
            ? new FormData(form, event.submitter)
            : new FormData(form);

        if (method === 'GET') {
            const submittedFields = new Set();
            for (const [key, value] of formData.entries()) {
                if (!submittedFields.has(key)) {
                    action.searchParams.delete(key);
                    submittedFields.add(key);
                }
                if (typeof value === 'string') action.searchParams.append(key, value);
            }
            const historyMode = form.querySelector('input[type="search"]') ? 'replace' : 'push';
            requestPage(action, { method: 'GET' }, historyMode);
            return;
        }

        requestPage(action, {
            method,
            body: formData,
        });
    });

    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement)) return;
        const form = input.form;
        if (!form || form.method.toUpperCase() !== 'GET' || !isEligibleForm(form)) return;

        clearTimeout(searchTimer);
        if (form.matches('[data-live-search]')) {
            if (input.type !== 'search' && !input.matches('[data-live-search-filter]')) return;
            searchTimer = setTimeout(() => updateLiveSearch(form), 180);
            return;
        }
        if (input.type !== 'search') return;
        searchTimer = setTimeout(() => {
            if (form.reportValidity()) form.requestSubmit();
        }, 350);
    });

    document.addEventListener('change', (event) => {
        const control = event.target;
        if (!(control instanceof HTMLSelectElement || control instanceof HTMLInputElement)
            || !control.matches('[data-live-search-filter]')) return;

        const form = control.form;
        if (!form?.matches('[data-live-search]') || !isEligibleForm(form)) return;
        clearTimeout(searchTimer);
        updateLiveSearch(form);
    });

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const target = event.target instanceof Element ? event.target : null;
        const searchFiltersToggle = target?.closest('[data-search-filters-toggle]');
        if (searchFiltersToggle) {
            const filters = document.getElementById(searchFiltersToggle.getAttribute('aria-controls'));
            if (filters) {
                const isExpanded = searchFiltersToggle.getAttribute('aria-expanded') === 'true';
                searchFiltersToggle.setAttribute('aria-expanded', String(!isExpanded));
                filters.hidden = isExpanded;
            }
            return;
        }

        const notificationToggle = target?.closest('[data-notifications-toggle]');
        if (notificationToggle) {
            const menu = notificationToggle.closest('[data-notifications]');
            const panel = menu?.querySelector('[data-notifications-panel]');
            if (panel) {
                const open = notificationToggle.getAttribute('aria-expanded') === 'true';
                notificationToggle.setAttribute('aria-expanded', String(!open));
                panel.hidden = open;
            }
            return;
        }

        const profileToggle = target?.closest('[data-profile-toggle]');
        if (profileToggle) {
            const menu = profileToggle.closest('.profile-container')?.querySelector('[data-profile-menu]');
            menu?.classList.toggle('is-open');
            return;
        }

        if (!target?.closest('[data-notifications]')) {
            document.querySelectorAll('[data-notifications]').forEach((menu) => {
                const toggle = menu.querySelector('[data-notifications-toggle]');
                const panel = menu.querySelector('[data-notifications-panel]');
                toggle?.setAttribute('aria-expanded', 'false');
                if (panel) panel.hidden = true;
            });
        }
        if (!target?.closest('.profile-container')) {
            document.querySelectorAll('[data-profile-menu].is-open').forEach((menu) => menu.classList.remove('is-open'));
        }

        const link = target?.closest('a[href]');
        if (!link || link.target && link.target !== '_self' || link.hasAttribute('download') || link.matches('[data-native-navigation]')) return;

        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin
            || destination.href === window.location.href
            || destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) {
            return;
        }

        event.preventDefault();
        requestPage(destination);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[data-search-filters-toggle][aria-expanded="true"]').forEach((toggle) => {
            toggle.setAttribute('aria-expanded', 'false');
            const filters = document.getElementById(toggle.getAttribute('aria-controls'));
            if (filters) filters.hidden = true;
            toggle.focus();
        });
        document.querySelectorAll('[data-notifications]').forEach((menu) => {
            const toggle = menu.querySelector('[data-notifications-toggle]');
            const panel = menu.querySelector('[data-notifications-panel]');
            const wasOpen = toggle?.getAttribute('aria-expanded') === 'true';
            toggle?.setAttribute('aria-expanded', 'false');
            if (panel) panel.hidden = true;
            if (wasOpen) toggle?.focus();
        });
        document.querySelectorAll('[data-profile-menu].is-open').forEach((menu) => menu.classList.remove('is-open'));
    });

    window.addEventListener('popstate', () => {
        requestPage(window.location.href, {}, 'none');
    });
})();

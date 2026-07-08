function getFormMethod(form) {
    return (form?.method || form?.getAttribute?.('method') || 'GET').toUpperCase();
}

function getDatasetValue(element, key) {
    return element?.dataset?.[key] ?? element?.getAttribute?.(`data-${key.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`)}`);
}

function getFormControls(form) {
    return Array.from(form?.elements ?? []);
}

function hasNamedControl(form, name) {
    return getFormControls(form).some((control) => control?.name === name);
}

function collectFormEntries(form) {
    if (typeof HTMLFormElement !== 'undefined' && form instanceof HTMLFormElement) {
        return Array.from(new FormData(form).entries());
    }

    return getFormControls(form).flatMap((control) => {
        if (!control?.name || control.disabled) {
            return [];
        }

        const type = (control.type || '').toLowerCase();

        if (['button', 'submit', 'reset', 'file'].includes(type)) {
            return [];
        }

        if (['checkbox', 'radio'].includes(type) && !control.checked) {
            return [];
        }

        if (control.multiple && control.selectedOptions) {
            return Array.from(control.selectedOptions).map((option) => [control.name, option.value]);
        }

        return [[control.name, control.value ?? '']];
    });
}

export function isAjaxFilterFormEligible(form) {
    return Boolean(
        form
        && getDatasetValue(form, 'ajaxFilter') === 'true'
        && getDatasetValue(form, 'ajaxTarget')
        && getFormMethod(form) === 'GET'
        && !hasNamedControl(form, '_token')
        && !hasNamedControl(form, '_method')
    );
}

export function buildAjaxFormUrl(form, fallbackHref = globalThis.window?.location?.href ?? 'http://localhost/') {
    const baseHref = globalThis.window?.location?.origin ?? fallbackHref;
    const url = new URL(form?.action || fallbackHref, baseHref);
    const params = new URLSearchParams(url.search);
    const touchedKeys = new Set();

    collectFormEntries(form).forEach(([key, value]) => {
        if (!touchedKeys.has(key)) {
            params.delete(key);
            touchedKeys.add(key);
        }

        if (value !== '') {
            params.append(key, value);
        }
    });

    url.search = params.toString();

    return url;
}

export function buildAjaxPaginationUrl(linkHref, currentHref = globalThis.window?.location?.href ?? 'http://localhost/') {
    const url = new URL(linkHref, currentHref);
    const currentUrl = new URL(currentHref, url.origin);

    currentUrl.searchParams.forEach((value, key) => {
        if (key !== 'page' && !url.searchParams.has(key)) {
            url.searchParams.append(key, value);
        }
    });

    return url;
}

export function getAjaxFallbackUrl(url, fallbackHref = globalThis.window?.location?.href ?? 'http://localhost/') {
    return new URL(url || fallbackHref, fallbackHref).toString();
}

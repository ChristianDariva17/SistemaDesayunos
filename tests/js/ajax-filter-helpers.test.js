import test from 'node:test';
import assert from 'node:assert/strict';

import {
    buildAjaxFormUrl,
    buildAjaxPaginationUrl,
    getAjaxFallbackUrl,
    isAjaxFilterFormEligible,
} from '../../resources/js/ajax-filter-helpers.js';

function form(attributes = {}, elements = []) {
    return {
        action: attributes.action ?? 'https://example.test/admin/productos',
        method: attributes.method ?? 'GET',
        dataset: attributes.dataset ?? {
            ajaxFilter: 'true',
            ajaxTarget: '#productosResults',
        },
        elements,
    };
}

test('detects only GET ajax filter forms as eligible', () => {
    assert.equal(isAjaxFilterFormEligible(form()), true);
    assert.equal(isAjaxFilterFormEligible(form({ method: 'POST' })), false);
    assert.equal(isAjaxFilterFormEligible(form({ dataset: { ajaxFilter: 'false', ajaxTarget: '#productosResults' } })), false);
    assert.equal(isAjaxFilterFormEligible(form({ dataset: { ajaxFilter: 'true' } })), false);
});

test('serializes non-empty form inputs while preserving action query parameters', () => {
    const url = buildAjaxFormUrl(
        form(
            { action: 'https://example.test/admin/productos?sort=nombre&page=7' },
            [
                { name: 'search', value: 'cafe' },
                { name: 'categoria', value: 'bebidas' },
                { name: 'estado', value: '' },
            ],
        ),
    );

    assert.equal(url.toString(), 'https://example.test/admin/productos?sort=nombre&page=7&search=cafe&categoria=bebidas');
});

test('empty filter fields remove stale values from the action query string', () => {
    const url = buildAjaxFormUrl(
        form(
            { action: 'https://example.test/admin/clientes?search=ana&estado=activo' },
            [
                { name: 'search', value: '' },
                { name: 'estado', value: 'inactivo' },
            ],
        ),
    );

    assert.equal(url.toString(), 'https://example.test/admin/clientes?estado=inactivo');
});

test('pagination ajax urls preserve active filters from the current location', () => {
    const url = buildAjaxPaginationUrl(
        'https://example.test/admin/pedidos?page=3',
        'https://example.test/admin/pedidos?search=ana&estado=pendiente&page=2',
    );

    assert.equal(url.toString(), 'https://example.test/admin/pedidos?page=3&search=ana&estado=pendiente');
});

test('pagination ajax urls keep explicit link filters over current filters', () => {
    const url = buildAjaxPaginationUrl(
        'https://example.test/admin/pedidos?page=3&estado=completado',
        'https://example.test/admin/pedidos?search=ana&estado=pendiente&page=2',
    );

    assert.equal(url.toString(), 'https://example.test/admin/pedidos?page=3&estado=completado&search=ana');
});

test('csrf and spoofed action forms are not eligible for generic ajax interception', () => {
    assert.equal(isAjaxFilterFormEligible(form({}, [{ name: '_token', value: 'csrf-token' }])), false);
    assert.equal(isAjaxFilterFormEligible(form({}, [{ name: '_method', value: 'PATCH' }])), false);
    assert.equal(isAjaxFilterFormEligible(form({ method: 'DELETE' })), false);
});

test('fallback url calculation resolves explicit and missing destinations deterministically', () => {
    assert.equal(
        getAjaxFallbackUrl('/admin/productos?search=cafe', 'https://example.test/admin/productos'),
        'https://example.test/admin/productos?search=cafe',
    );
    assert.equal(
        getAjaxFallbackUrl('', 'https://example.test/admin/clientes?estado=activo'),
        'https://example.test/admin/clientes?estado=activo',
    );
});

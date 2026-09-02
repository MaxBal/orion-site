'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

class FakeElement {
    constructor(id, className) {
        this.id = id || '';
        this.className = className || '';
        this.children = [];
        this.parentElement = null;
        this.hidden = false;
        this.innerHTML = '';
    }

    appendChild(child) {
        child.parentElement = this;
        this.children.push(child);
        return child;
    }

    querySelectorAll(selector) {
        if (selector === '.mirror-row') {
            return this.children.filter((child) => child.className.split(/\s+/).includes('mirror-row'));
        }
        return [];
    }

    remove() {
        if (!this.parentElement) return;
        const index = this.parentElement.children.indexOf(this);
        if (index !== -1) this.parentElement.children.splice(index, 1);
        this.parentElement = null;
    }
}

function mirrorRow() {
    return new FakeElement('', 'mirror-row');
}

function removalButton(row) {
    return {
        closest(selector) {
            return selector === '.mirror-row' ? row : null;
        },
    };
}

function createHarness(clientRows, patchRows) {
    const listeners = {};
    const elements = new Map();
    const client = new FakeElement('client-mirrors', 'mirror-list');
    const patch = new FakeElement('patch-mirrors', 'mirror-list');
    const clientAdd = new FakeElement('btn-add-client', 'mirror-add');
    const patchAdd = new FakeElement('btn-add-patch', 'mirror-add');

    for (let i = 0; i < clientRows; i += 1) client.appendChild(mirrorRow());
    for (let i = 0; i < patchRows; i += 1) patch.appendChild(mirrorRow());
    [client, patch, clientAdd, patchAdd].forEach((element) => elements.set(element.id, element));

    const document = {
        addEventListener(type, callback) {
            listeners[type] = callback;
        },
        createElement() {
            return new FakeElement();
        },
        getElementById(id) {
            return elements.get(id) || null;
        },
        querySelector() {
            return null;
        },
        querySelectorAll(selector) {
            if (selector === '.js-player-mode') return [];
            if (selector === '#client-mirrors .mirror-row') return client.querySelectorAll('.mirror-row');
            if (selector === '#patch-mirrors .mirror-row') return patch.querySelectorAll('.mirror-row');
            return [];
        },
    };

    const window = {
        OrionAdminConfig: {},
        clearTimeout() {},
        confirm() { return true; },
        location: { reload() {} },
        matchMedia() {
            return { matches: false, addEventListener() {}, addListener() {} };
        },
        setTimeout() {},
    };

    const source = fs.readFileSync(path.join(__dirname, '..', 'js', 'admin.js'), 'utf8');
    vm.runInNewContext(source, { window, document, console, FormData: class {}, fetch() {} });
    listeners.DOMContentLoaded();

    return { window, client, patch, clientAdd, patchAdd };
}

const harness = createHarness(5, 4);

assert.strictEqual(harness.window.mirrorCount.client, 5, 'initial client count should come from the DOM');
assert.strictEqual(harness.clientAdd.hidden, true, 'initial five-row client add button should be hidden');

const savedClientRow = harness.client.children[0];
harness.window.removeMirrorRow(removalButton(savedClientRow));
assert.strictEqual(harness.window.mirrorCount.client, 4, 'removing a saved row should refresh the count');
assert.strictEqual(harness.clientAdd.hidden, false, 'removing a saved row should restore the add button');

harness.window.addMirrorRow('client-mirrors', 'client');
assert.strictEqual(harness.client.children.length, 5, 'a client row should be addable after removal');
assert.strictEqual(harness.clientAdd.hidden, true, 'adding the fifth client row should hide the add button');

assert.strictEqual(harness.patchAdd.hidden, false, 'four patch rows should leave the add button visible');
harness.window.addMirrorRow('patch-mirrors', 'patch');
const dynamicPatchRow = harness.patch.children[4];
assert.strictEqual(harness.window.mirrorCount.patch, 5, 'adding a fifth patch row should refresh the count');
assert.strictEqual(harness.patchAdd.hidden, true, 'adding the fifth patch row should hide the add button');
assert.match(dynamicPatchRow.innerHTML, /name="patch_name\[\]"/, 'dynamic mirror name field changed');
assert.match(dynamicPatchRow.innerHTML, /name="patch_url\[\]"/, 'dynamic mirror URL field changed');
assert.match(dynamicPatchRow.innerHTML, /name="patch_enabled\[\]" checked/, 'dynamic mirror enabled field changed');
assert.match(dynamicPatchRow.innerHTML, /onclick="removeMirrorRow\(this\)"/, 'dynamic mirror does not use shared removal');

harness.window.removeMirrorRow(removalButton(dynamicPatchRow));
assert.strictEqual(harness.window.mirrorCount.patch, 4, 'removing the dynamic fifth row should refresh the count');
assert.strictEqual(harness.patchAdd.hidden, false, 'removing the dynamic fifth row should restore the add button');

harness.window.addMirrorRow('patch-mirrors', 'patch');
harness.window.addMirrorRow('patch-mirrors', 'patch');
assert.strictEqual(harness.patch.children.length, 5, 'mirror lifecycle must never allow a sixth row');

process.stdout.write('Admin mirror lifecycle checks passed.\n');

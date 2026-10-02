/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

import {register} from 'di';
import Ui from 'ui';

/** @typedef {import('view').default} View */
/** @typedef {string|function(KeyboardEvent): void} Key */

@register()
export default class ShortcutManager {

    /**
     * @private
     * @type {number}
     */
    level = 0

    /**
     * @private
     * @type {{
     *     view: View[],
     *     keys: Record.<string, Key>,
     *     level: number,
     * }[]}
     */
    items

    constructor() {
        this.items = [];

        document.addEventListener('keydown', event => this.handle(event), {capture: true});
    }

    /**
     * Add a view and keys.
     *
     * @param {import('view').default<any>} view
     * @param {Record.<string, Key>} keys
     * @param {{stack: boolean}} [options]
     */
    add(view, keys, options = {}) {
        if (this.items.find(it => it.view === view)) {
            return;
        }

        if (options.stack) {
            this.level ++;
        }

        this.items.push({
            view: view,
            keys: keys,
            level: this.level,
        });
    }

    /**
     * Remove a view.
     *
     * @param {import('view').default<any>} view
     */
    remove(view) {
        const index = this.items.findIndex(it => it.view === view);

        if (index < 0) {
            return;
        }

        this.items.splice(index, 1);

        let maxLevel = 0;

        for (const item of this.items) {
            if (item.level > maxLevel) {
                maxLevel = item.level;
            }
        }

        this.level = maxLevel;
    }

    /**
     * Handle.
     *
     * @param {KeyboardEvent} event
     */
    handle(event) {
        const items = this.items.filter(it => it.level === this.level);

        if (items.length === 0) {
            return;
        }

        if (Ui.getConfirmCount()) {
            return;
        }

        const key = Espo.Utils.getKeyFromKeyEvent(event);

        for (const item of items) {
            const subject = item.keys[key];

            if (!subject) {
                continue;
            }

            if (typeof subject === 'function') {
                subject.call(item.view, event);

                break;
            }

            event.preventDefault();
            event.stopPropagation();

            const methodName = 'action' + Espo.Utils.upperCaseFirst(subject);

            if (typeof item.view[methodName] === 'function') {
                item.view[methodName]();

                break;
            }
        }
    }
}

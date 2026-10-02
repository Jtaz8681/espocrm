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

import ArrayFieldView from 'views/fields/array';
import ReactionsHelper from 'helpers/misc/reactions';

// noinspection JSUnusedGlobalSymbols
export default class extends ArrayFieldView {

    /**
     * @type {Object.<string, string>}
     * @private
     */
    iconClassMap

    /**
     * @private
     * @type {ReactionsHelper}
     */
    reactionsHelper

    setup() {
        this.reactionsHelper = new ReactionsHelper();

        this.iconClassMap = this.reactionsHelper.getDefinitionList().reduce((o, it) => {
            o[it.type] = it.iconClass;

            return o;
        }, {});

        super.setup();
    }

    setupOptions() {
        const list = this.reactionsHelper.getDefinitionList();

        this.params.options = list.map(it => it.type);

        this.translatedOptions = list.reduce((o, it) => {
            o[it.type] = this.translate(it.type, 'reactions');

            return o;
        }, {});
    }

    /**
     * @param {string} value
     * @return {string}
     */
    getItemHtml(value) {
        const html = super.getItemHtml(value);

        const item = /** @type {HTMLElement} */
            new DOMParser().parseFromString(html, 'text/html').body.childNodes[0];

        const icon = this.createIconElement(value);

        item.querySelector('.text').prepend(icon);

        return item.outerHTML;
    }

    /**
     * @private
     * @param {string} value
     * @return {HTMLSpanElement}
     */
    createIconElement(value) {
        const icon = document.createElement('span');

        (this.iconClassMap[value] || '')
            .split(' ')
            .filter(it => it)
            .forEach(it => icon.classList.add(it));

        icon.classList.add('text-soft');
        icon.style.display = 'inline-block';
        icon.style.width = 'var(--24px)';

        return icon;
    }

    /**
     * @inheritDoc
     */
    async actionAddItem() {
        const view = await super.actionAddItem();

        view.whenRendered().then(() => {
            const anchors = /** @type {HTMLAnchorElement[]} */
                view.element.querySelectorAll(`a[data-value]`);

            anchors.forEach(a => {
                const icon = this.createIconElement(a.dataset.value);

                a.prepend(icon);
            });
        });

        return view;
    }
}

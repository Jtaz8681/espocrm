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

import View from 'view';

class CollapsedModalView extends View {

    templateContent = `
        <div class="title-container">
            <a role="button" data-action="expand" class="title">{{title}}</a>
        </div>
        <div class="close-container">
            <a role="button" data-action="close"><span class="fas fa-times"></span></a>
        </div>
    `

    events = {
        /** @this CollapsedModalView */
        'click [data-action="expand"]': function () {
            this.expand();
        },
        /** @this CollapsedModalView */
        'click [data-action="close"]': function () {
            this.close();
        },
    }

    /**
     * @private
     */
    title

    /**
     * @type {import('views/modal').default|import('views/popup-notification').default}
     */
    modalView

    /**
     * @param {{
     *     modalView: import('views/modal').default|import('views/popup-notification').default,
     *     onClose: function(),
     *     onExpand: function(),
     *     duplicateNumber?: number|null,
     *     title?: string | null,
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;

        this.modalView = options.modalView;
    }

    data() {
        let title = this.title;

        if (this.options.duplicateNumber) {
            title = `${this.title} ${this.options.duplicateNumber}`;
        }

        return {
            title: title,
        };
    }

    setup() {
        this.title = this.options.title || 'no-title';
    }

    expand() {
        this.options.onExpand();
    }

    close() {
        this.options.onClose();
    }
}

// noinspection JSUnusedGlobalSymbols
export default CollapsedModalView;

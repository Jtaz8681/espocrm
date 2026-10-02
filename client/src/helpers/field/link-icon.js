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

/**
 * @since 9.3.1
 */
export default class LinkFieldIconHelper {

    /**
     * @param {import('views/fields/link').default} view
     * @param {{
     *     iconClass: string,
     *     getIconClass: function(): string|null,
     *     getColor: function(): string,
     * }} options
     */
    constructor(view, options) {
        this.view = view;
        this.options = options;

        view.listenTo(view, 'after:render', () => {
            if (view.isEditMode()) {
                this.control();
            }
        });

        view.addHandler('keydown', `input[data-name="${view.nameName}"]`, (/** KeyboardEvent */e, target) => {
            if (e.code === 'Enter') {
                return;
            }

            target.classList.add('being-typed');
        });

        view.addHandler('change', `input[data-name="${view.nameName}"]`, (e, target) => {
            setTimeout(() => target.classList.remove('being-typed'), 200);
        });

        view.addHandler('blur', `input[data-name="${view.nameName}"]`, (e, target) => {
            target.classList.remove('being-typed');
        });

        view.on('change', () => {
            if (!view.isEditMode()) {
                return;
            }

            const span = view.element.querySelector('span.icon-in-input');

            if (span) {
                span.parentNode.removeChild(span);
            }

            setTimeout(() => this.control(), 0);
        });
    }

    /**
     * @private
     */
    control() {
        const view = this.view;

        const nameElement = view.element.querySelector(`input[data-name="${view.nameName}"]`);
        nameElement.classList.remove('being-typed');

        const icon = document.createElement('span');
        icon.className = 'icon-in-input ' + this.options.iconClass;
        icon.style.color = this.options.getColor();

        const iconClass = this.options.getIconClass();

        if (!iconClass) {
            return;
        }

        icon.className += ' ' + iconClass;

        const input = view.element.querySelector('.input-group > input');

        if (!input) {
            return;
        }

        input.after(icon);
    }
}

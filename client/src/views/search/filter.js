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

/** @module views/search/filter */

import View from 'view';

class FilterView extends View {

    template = 'search/filter'

    data() {
        return {
            name: this.name,
            scope: this.model.entityType,
            notRemovable: this.options.notRemovable,
        };
    }

    /**
     * @param {{
     *     name: string,
     *     viewName?: string|null,
     *     params?: Record|null,
     *     notRemovable?: boolean,
     *     model: import('model').default,
     * }} options Field view is supported as of v9.3.
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        const name = this.name = this.options.name;

        let viewName = this.options.viewName;

        if (!viewName) {
            let type = this.model.getFieldType(name);

            if (!type && name === 'id') {
                type = 'id';
            }

            if (type) {
                viewName = this.model.getFieldParam(name, 'view') ||
                    this.getFieldManager().getViewName(type);
            }
        }

        if (!viewName) {
            return;
        }

        this.createView('field', viewName, {
            mode: 'search',
            model: this.model,
            selector: '.field',
            name: name,
            searchParams: this.options.params,
        }, view => {
            this.listenTo(view, 'change', () => this.trigger('change'));
            this.listenTo(view, 'search', () => this.trigger('search'));
        });
    }

    /**
     * @return {import('views/fields/base').default|null}
     */
    getFieldView() {
        return this.getView('field');
    }

    populateDefaults() {
        const view = this.getFieldView();

        if (!view) {
            return;
        }

        if (!('populateSearchDefaults' in view)) {
            return;
        }

        view.populateSearchDefaults();
    }
}

export default FilterView;

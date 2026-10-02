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

class GlobalSearchPanel extends View {

    template = 'global-search/panel'

    setup() {
        this.addHandler('click', '[data-action="closePanel"]', () => this.close());

        this.maxSize = this.getConfig().get('globalSearchMaxSize') || 10;
    }

    afterRender() {
        this.collection.reset();
        this.collection.maxSize = this.maxSize;

        this.collection.fetch()
            .then(() => this.createRecordView())
            .then(view => view.render());
    }

    /**
     * @return {Promise<module:views/record/list-expanded>}
     */
    createRecordView() {
        // noinspection JSValidateTypes
        return this.createView('list', 'views/record/list-expanded', {
            selector: '.list-container',
            collection: this.collection,
            listLayout: {
                rows: [
                    [
                        {
                            name: 'name',
                            view: 'views/global-search/name-field',
                        }
                    ],
                    [
                        {
                            name: 'status',
                            view: 'views/global-search/status-field',
                        }
                    ]
                ],
                right: {
                    name: 'read',
                    view: 'views/global-search/scope-badge',
                    width: '80px',
                },
            }
        });
    }

    close() {
        this.trigger('close');
    }
}

export default GlobalSearchPanel;

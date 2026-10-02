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

import EnumFieldView from 'views/fields/enum';

export default class extends EnumFieldView {

    setup() {
        super.setup();

        this.on('change', () => {
            const o = {
                primaryFilter: null,
                boolFilterList: [],
                title: this.translate('Records', 'dashlets'),
                sortBy: null,
                sortDirection: 'asc',
            };

            o.expandedLayout = {
                rows: []
            };

            const entityType = this.model.get('entityType');

            if (entityType) {
                o.title = this.translate(entityType, 'scopeNamesPlural');
                o.sortBy = this.getMetadata().get(['entityDefs', entityType, 'collection', 'orderBy']);

                const order = this.getMetadata().get(['entityDefs', entityType, 'collection', 'order']);

                if (order) {
                    o.sortDirection = order;
                } else {
                    o.sortDirection = 'asc';
                }

                o.expandedLayout = {
                    rows: [[{name: "name", link: true, scope: entityType}]]
                };
            }

            this.model.set(o);
        });
    }

    setupOptions() {
        this.params.options =  Object.keys(this.getMetadata().get('scopes'))
            .filter(scope => {
                if (this.getMetadata().get(`scopes.${scope}.disabled`)) {
                    return;
                }

                if (!this.getAcl().checkScope(scope, 'read')) {
                    return;
                }

                if (!this.getMetadata().get(['scopes', scope, 'entity'])) {
                    return;
                }

                if (!this.getMetadata().get(['scopes', scope, 'object'])) {
                    return;
                }

                return true;
            })
            .sort((v1, v2) => {
                return this.translate(v1, 'scopeNames').localeCompare(this.translate(v2, 'scopeNames'));
            });

        this.params.options.unshift('');
    }
}

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

import MultiEnumFieldView from 'views/fields/multi-enum';

export default class extends MultiEnumFieldView {

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:entityType', () => {
            this.setupOptions();
            this.reRender();
        });
    }

    setupOptions() {
        const entityType = this.model.get('entityType');

        if (!entityType) {
            this.params.options = [];

            return;
        }

        const filterList = this.getMetadata().get(['clientDefs', entityType, 'boolFilterList']) || [];

        this.params.options = [];

        filterList.forEach(item => {
            if (typeof item === 'object' && item.name) {
                if (
                    item.accessDataList &&
                    !Espo.Utils.checkAccessDataList(item.accessDataList, this.getAcl(), this.getUser(), null, true)
                ) {
                    return false;
                }

                this.params.options.push(item.name);

                return;
            }

            this.params.options.push(item);
        });

        if (
            this.getMetadata().get(['scopes', entityType, 'stream']) &&
            this.getAcl().checkScope(entityType, 'stream')
        ) {
            this.params.options.push('followed');
        }

        if (this.getMetadata().get(`scopes.${entityType}.collaborators`)) {
            this.params.options.push('shared');
        }

        this.translatedOptions = {};

        this.params.options.forEach(item => {
            this.translatedOptions[item] = this.translate(item, 'boolFilters', entityType);
        });
    }
}

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

import SelectRecordsModalView from 'views/modals/select-records';

class SelectTemplateModalView extends SelectRecordsModalView {

    multiple = false
    createButton = false
    searchPanel = false
    scope = 'Template'
    backdrop = true

    setupSearch() {
        super.setupSearch();

        this.searchManager.setAdvanced({
            entityType: {
                type: 'equals',
                value: this.options.entityType,
            },
        });

        this.collection.where = this.searchManager.getWhere();

        this.collection.data.primaryFilter = 'active';
    }

    afterRender() {
        super.afterRender();

        const firstLinkElement = this.$el.find('a.link').first().get(0);

        if (firstLinkElement) {
            // noinspection JSUnresolvedReference
            setTimeout(() => firstLinkElement.focus({preventScroll: true}), 10);
        }
    }
}

export default SelectTemplateModalView;

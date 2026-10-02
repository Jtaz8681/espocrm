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

import MainView from 'views/main';

class MergeView extends MainView {

    template = 'merge'

    name = 'Merge'

    headerView = 'views/header'
    recordView = 'views/record/merge'

    setup() {
        this.models = this.options.models;

        this.setupHeader();
        this.setupRecord();
    }

    setupHeader() {
        this.createView('header', this.headerView, {
            model: this.model,
            fullSelector: '#main > .page-header'
        });
    }

    setupRecord() {
        this.createView('body', this.recordView, {
            fullSelector: '#main > .body',
            models: this.models,
            collection: this.collection
        });
    }

    getHeader() {
        return this.buildHeaderHtml([
            $('<a>')
                .attr('href', '#' + this.models[0].entityType)
                .text(this.getLanguage().translate(this.models[0].entityType, 'scopeNamesPlural')),
            $('<span>')
                .text(this.getLanguage().translate('Merge'))
        ]);
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Merge'));
    }
}

export default MergeView;

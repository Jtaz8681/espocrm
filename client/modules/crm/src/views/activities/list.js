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

import ListRelatedView from 'views/list-related';

export default class ActivitiesListView extends ListRelatedView {

    createButton = false
    unlinkDisabled = true
    filtersDisabled = true
    allResultDisabled = true;

    setup() {
        this.rowActionsView = 'views/record/row-actions/default';

        super.setup();

        this.type = this.options.type;
    }

    getHeader() {
        const name = this.model.get('name') || this.model.id;

        const recordUrl = `#${this.scope}/view/${this.model.id}`;

        const $name =
            $('<a>')
                .attr('href', recordUrl)
                .addClass('font-size-flexible title')
                .text(name)
                .css('user-select', 'none');

        if (this.model.get('deleted')) {
            $name.css('text-decoration', 'line-through');
        }

        const headerIconHtml = this.getHelper().getScopeColorIconHtml(this.foreignScope);
        const scopeLabel = this.getLanguage().translate(this.scope, 'scopeNamesPlural');

        let $root = $('<span>').text(scopeLabel);

        if (!this.rootLinkDisabled) {
            $root = $('<span>')
                .append(
                    $('<a>')
                        .attr('href', '#' + this.scope)
                        .addClass('action')
                        .attr('data-action', 'navigateToRoot')
                        .text(scopeLabel)
                );
        }

        $root.css('user-select', 'none');

        if (headerIconHtml) {
            $root.prepend(headerIconHtml);
        }

        const linkLabel = this.type === 'history' ? this.translate('History') : this.translate('Activities');

        const $link = $('<span>').text(linkLabel);

        $link
            .css('user-select', 'none');

        const $target = $('<span>').text(this.translate(this.foreignScope, 'scopeNamesPlural'));

        $target
            .css('user-select', 'none')
            .css('cursor', 'pointer')
            .attr('data-action', 'fullRefresh')
            .attr('title', this.translate('clickToRefresh', 'messages'))

        return this.buildHeaderHtml([
            $root,
            $name,
            $link,
            $target,
        ]);
    }

    /**
     * @inheritDoc
     */
    updatePageTitle() {
        this.setPageTitle(this.translate(this.foreignScope, 'scopeNamesPlural'));
    }
}

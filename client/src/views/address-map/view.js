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

class AddressMapView extends MainView {

    templateContent = `
        <div class="header page-header">{{{header}}}</div>
        <div class="map-container">{{{map}}}</div>
    `

    setup() {
        this.scope = this.model.entityType;

        this.createView('header', 'views/header', {
            model: this.model,
            fullSelector: '#main > .header',
            scope: this.model.entityType,
            fontSizeFlexible: true,
        });
    }

    afterRender() {
        const field = this.options.field;

        const viewName = this.model.getFieldParam(field + 'Map', 'view') ||
            this.getFieldManager().getViewName('map');

        this.createView('map', viewName, {
            model: this.model,
            name: field + 'Map',
            selector: '.map-container',
            height: this.getHelper().calculateContentContainerHeight(this.$el.find('.map-container')),
        }, view => {
            view.render();
        });
    }

    getHeader() {
        let name = this.model.get('name');

        if (!name) {
            name = this.model.id;
        }

        const recordUrl = `#${this.model.entityType}/view/${this.model.id}`;
        const scopeLabel = this.getLanguage().translate(this.model.entityType, 'scopeNamesPlural');
        const fieldLabel = this.translate(this.options.field, 'fields', this.model.entityType);

        const rootUrl = this.options.rootUrl ||
            this.options.params.rootUrl ||
            '#' + this.model.entityType;

        const $name = $('<a>')
            .attr('href', recordUrl)
            .append(
                $('<span>')
                    .addClass('font-size-flexible title')
                    .text(name)
            );

        if (this.model.get('deleted')) {
            $name.css('text-decoration', 'line-through');
        }

        const $root = $('<span>')
            .append(
                $('<a>')
                    .attr('href', rootUrl)
                    .addClass('action')
                    .attr('data-action', 'navigateToRoot')
                    .text(scopeLabel)
            );

        const headerIconHtml = this.getHeaderIconHtml();

        if (headerIconHtml) {
            $root.prepend(headerIconHtml);
        }

        const $field = $('<span>').text(fieldLabel);

        return this.buildHeaderHtml([
            $root,
            $name,
            $field,
        ]);
    }
}

export default AddressMapView

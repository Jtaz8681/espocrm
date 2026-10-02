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

import ModalView from 'views/modal';

class ViewMapModalView extends ModalView {

    templateContent = `<div class="map-container no-side-margin">{{{map}}}</div>`

    backdrop = true

    setup() {
        const field = this.options.field;

        const url = '#AddressMap/view/' + this.model.entityType + '/' + this.model.id + '/' + field;
        const fieldLabel = this.translate(field, 'fields', this.model.entityType);

        this.headerElement =
            $('<a>')
                .attr('href', '#' + url)
                .text(fieldLabel)
                .get(0);

        const viewName = this.model.getFieldParam(field + 'Map', 'view') ||
            this.getFieldManager().getViewName('map');

        this.createView('map', viewName, {
            model: this.model,
            name: field + 'Map',
            selector: '.map-container',
            height: 'auto',
        });
    }
}

export default ViewMapModalView;

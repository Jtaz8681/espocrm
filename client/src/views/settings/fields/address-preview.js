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

import AddressFieldView from 'views/fields/address';

export default class extends AddressFieldView {

    setup() {
        super.setup();

        const mainModel = this.model;
        const model = mainModel.clone();

        model.entityType = mainModel.entityType;
        model.name = mainModel.name;

        model.set({
            addressPreviewStreet: 'Street',
            addressPreviewPostalCode: 'PostalCode',
            addressPreviewCity: 'City',
            addressPreviewState: 'State',
            addressPreviewCountry: 'Country',
        });

        this.listenTo(mainModel, 'change:addressFormat', () => {
            model.set('addressFormat', mainModel.get('addressFormat'));

            this.reRender();
        });

        this.model = model;
    }

    getAddressFormat() {
        return this.model.get('addressFormat') || 1;
    }
}

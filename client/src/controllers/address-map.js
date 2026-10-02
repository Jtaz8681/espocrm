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

import Controller from 'controller';

class AddressMapController extends Controller {

    defaultAction = 'index'

    // noinspection JSUnusedGlobalSymbols
    actionIndex() {
        this.error404();
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {Object} o
     */
    actionView(o) {
        this.modelFactory
            .create(o.entityType)
            .then(model => {
                model.id = o.id;

                model.fetch()
                    .then(() => {
                        const viewName = this.getMetadata().get(['AddressMap', 'view']) ||
                            'views/address-map/view';

                        this.main(viewName, {
                            model: model,
                            field: o.field,
                        });
                    });
            });
    }
}

export default AddressMapController;

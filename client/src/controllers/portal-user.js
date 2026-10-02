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

import RecordController from 'controllers/record';

class PortalUserController extends RecordController {

    entityType = 'User'

    getCollection(usePreviouslyFetched) {
        return super.getCollection()
            .then(collection => {
                collection.data.userType = 'portal';

                return collection;
            });
    }

    /**
     * @protected
     * @param {Object} options
     * @param {module:models/user} model
     * @param {string} view
     */
    createViewView(options, model, view) {
        if (!model.isPortal()) {
            if (model.isApi()) {
                this.getRouter().dispatch('ApiUser', 'view', {id: model.id, model: model});

                return;
            }

            this.getRouter().dispatch('User', 'view', {id: model.id, model: model});

            return;
        }

        super.createViewView(options, model, view);
    }

    actionCreate(options) {
        options = options || {};
        options.attributes = options.attributes  || {};
        options.attributes.type = 'portal';

        super.actionCreate(options);
    }

    checkAccess(action) {
        if (this.getAcl().getPermissionLevel('portalPermission') === 'yes') {
            return true;
        }

        return false;
    }
}

export default PortalUserController;

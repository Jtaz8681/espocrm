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

class UserController extends RecordController {

    getCollection(usePreviouslyFetched) {
        return super.getCollection()
            .then(collection => {
                collection.data.userType = 'internal';

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
        if (model.get('deleted')) {
            view = 'views/deleted-detail';

            super.createViewView(options, model, view);

            return;
        }

        if (model.isPortal()) {
            this.getRouter().dispatch('PortalUser', 'view', {id: model.id, model: model});

            return;
        }

        if (model.isApi()) {
            this.getRouter().dispatch('ApiUser', 'view', {id: model.id, model: model});

            return;
        }

        super.createViewView(options, model, view);
    }
}

export default UserController;

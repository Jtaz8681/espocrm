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

class ExternalAccountController extends Controller {

    defaultAction = 'list'

    actionList() {
        this.collectionFactory.create('ExternalAccount', collection => {
            collection.once('sync', () => {
                this.main('ExternalAccount.Index', {
                    collection: collection,
                });
            });

            collection.fetch();
        });
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {{id: string}} options
     */
    actionEdit(options) {
        const id = options.id;

        this.collectionFactory.create('ExternalAccount', collection => {
            collection.once('sync', () => {
                this.main('ExternalAccount.Index', {
                    collection: collection,
                    id: id,
                });
            });

            collection.fetch();
        });
    }
}

export default ExternalAccountController;

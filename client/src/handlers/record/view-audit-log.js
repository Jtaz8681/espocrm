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

import StreamViewAuditLogModalView from 'views/stream/modals/view-audit-log';

class ViewAuditLogHandler {

    constructor(/** import('views/record/detail').default */view) {
        this.view = view;
        this.metadata = /** @type {module:metadata} */view.getMetadata();
        this.entityType = this.view.entityType;
        this.model = /** @type {module:model} */this.view.model;

        this.hasAudited =
            this.metadata.get(`scopes.${this.entityType}.statusField`) ||
            this.model.getFieldList().find(field => this.model.getFieldParam(field, 'audited')) !== undefined;

        if (this.entityType === 'User' && !this.view.getUser().isAdmin()) {
            this.hasAudited = false;
        }

        if (this.view.getUser().isPortal()) {
            this.hasAudited = false;
        }

        if (this.view.getAcl().getPermissionLevel('audit') !== 'yes') {
            this.hasAudited = false;
        }
    }

    isAvailable() {
        return this.hasAudited;
    }

    show() {
        const view = new StreamViewAuditLogModalView({model: this.model});

        this.view.assignView('dialog', view)
            .then(() => {
                view.render();
            })
    }
}

// noinspection JSUnusedGlobalSymbols
export default ViewAuditLogHandler;


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

import ActionHandler from 'action-handler';
import {inject} from 'di';
import AclManager from 'acl-manager';
import Metadata from 'metadata';

// noinspection JSUnusedGlobalSymbols
export default class LockActionHandler extends ActionHandler {

    /**
     * @type {AclManager}
     */
    @inject(AclManager)
    acl

    /**
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    async actionLock() {
        const model = this.view.model;

        Espo.Ui.notifyWait();

        let attributes;

        try {
            attributes = await Espo.Ajax.postRequest('Action', {
                action: 'lock',
                entityType: model.entityType,
                id: model.id,
            });
        } catch (e) {
            return;
        }

        model.setMultiple(attributes, {sync: true});
        model.trigger('sync', this.model, null, {});

        Espo.Ui.success(this.view.translate('locked', 'messages'));
    }

    async actionUnlock() {
        const model = this.view.model;

        Espo.Ui.notifyWait();

        let attributes;

        try {
            attributes = await Espo.Ajax.postRequest('Action', {
                action: 'unlock',
                entityType: model.entityType,
                id: model.id,
            });
        } catch (e) {
            return;
        }

        model.setMultiple(attributes, {sync: true});
        model.trigger('sync', this.model, null, {});

        Espo.Ui.success(this.view.translate('unlocked', 'messages'));
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @return {boolean}
     */
    canBeLocked() {
        const model = this.view.model;

        if (!this.isEnabled(model.entityType)) {
            return false;
        }

        if (this.acl.getPermissionLevel('lock') !== 'yes') {
            return false;
        }

        if (model.attributes.isLocked) {
            return false;
        }

        return true;
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @return {boolean}
     */
    canBeUnlocked() {
        const model = this.view.model;

        if (!this.isEnabled(model.entityType)) {
            return false;
        }

        if (this.acl.getPermissionLevel('lock') !== 'yes') {
            return false;
        }

        if (!model.attributes.isLocked) {
            return false;
        }

        return true;
    }

    /**
     * @private
     * @param {string} entityType
     * @return {boolean}
     */
    isEnabled(entityType) {
        return this.metadata.get(`scopes.${entityType}.lockable`) === true;
    }
}

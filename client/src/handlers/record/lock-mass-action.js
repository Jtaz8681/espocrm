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
import Language from 'language';
import MassActionHelper from 'helpers/mass-action';
import Metadata from 'metadata';

// noinspection JSUnusedGlobalSymbols
export default class LockMassActionHandler extends ActionHandler {

    /**
     * @type {AclManager}
     */
    @inject(AclManager)
    acl

    /**
     * @type {Language}
     */
    @inject(Language)
    language

    /**
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    // noinspection JSUnusedGlobalSymbols
    async actionLock() {
        const msg = this.language.translate('confirmMassLock', 'messages');

        await this.view.confirm(msg);

        await this.process('lock');
    }

    // noinspection JSUnusedGlobalSymbols
    async actionUnlock() {
        const msg = this.language.translate('confirmMassUnlock', 'messages');

        await this.view.confirm(msg);

        await this.process('unlock');
    }

    /**
     * @private
     * @param {string} action
     */
    async process(action) {
        const helper = new MassActionHelper(this.view);
        const params = this.view.getMassActionSelectionPostData();
        const idle = !!params.searchParams && helper.checkIsIdle(this.view.collection.total);

        const onDone = count => {
            const labelKey = action === 'lock' ? 'massLockDone': 'massUnlockDone';

            const msg = this.view.translate(labelKey, 'messages')
                .replace('{count}', count.toString());

            Espo.Ui.success(msg);
        };

        Espo.Ui.notifyWait();

        const result = await Espo.Ajax.postRequest('MassAction', {
            entityType: this.view.entityType,
            action: action,
            params: params,
            idle: idle,
        });

        if (result.id) {
            const view = await helper.process(result.id, action)

            this.view.listenToOnce(view, 'close:success', result => onDone(result.count));

            return;
        }

        onDone(result.count);
    }

    // noinspection JSUnusedGlobalSymbols
    initLock() {
        if (
            !this.view.collection ||
            !this.isEnabled(this.view.collection.entityType) ||
            this.acl.getPermissionLevel('massUpdate') !== 'yes' ||
            this.acl.getPermissionLevel('lock') !== 'yes'
        ) {
            this.view.removeMassAction('lock');
        }
    }

    // noinspection JSUnusedGlobalSymbols
    initUnlock() {
        if (
            !this.view.collection ||
            !this.isEnabled(this.view.collection.entityType) ||
            this.acl.getPermissionLevel('massUpdate') !== 'yes' ||
            this.acl.getPermissionLevel('lock') !== 'yes'
        ) {
            this.view.removeMassAction('unlock');
        }
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

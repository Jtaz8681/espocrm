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

/** @module acl */

import {View as BullView} from 'bullbone';

/**
 * Internal class for access checking. Can be extended to customize access checking
 * for a specific scope.
 */
class Acl {

    /**
     * @param {module:models/user} user A user.
     * @param {string} scope A scope.
     * @param {Object} params Parameters.
     * @param {import('acl-manager').default} aclManager
     */
    constructor(user, scope, params, aclManager) {
        /**
         * A user.
         *
         * @type {module:models/user|null}
         * @protected
         */
        this.user = user || null;
        this.scope = scope;

        params = params || {};

        this.aclAllowDeleteCreated = params.aclAllowDeleteCreated;
        this.teamsFieldIsForbidden = params.teamsFieldIsForbidden;

        /**
         * @type {string[]}
         */
        this.forbiddenFieldList = params.forbiddenFieldList || [];

        /**
         * @protected
         * @type {boolean}
         */
        this.collaboratorsFieldIsForbidden = this.forbiddenFieldList.includes('collaborators');

        /**
         * @type {import('acl-manager').default}
         * @private
         */
        this._aclManager = aclManager;
    }

    /**
     * Get a user.
     *
     * @returns {module:models/user}
     * @protected
     */
    getUser() {
        return this.user;
    }

    /**
     * Check access to a scope.
     *
     * @param {string|boolean|Object.<string, string>} data Access data.
     * @param {module:acl-manager~action|null} [action=null] An action.
     * @param {boolean} [precise=false] To return `null` if `inTeam == null`.
     * @param {Record.<string, boolean|null>|null} [entityAccessData=null] Entity access data. `inTeam`, `isOwner`.
     * @returns {boolean|null} True if access allowed.
     */
    checkScope(data, action, precise, entityAccessData) {
        entityAccessData = entityAccessData || {};

        const inTeam = entityAccessData.inTeam;
        const isOwner = entityAccessData.isOwner;
        const isShared = entityAccessData.isShared;

        if (this.getUser().isAdmin()) {
            if (data === false) {
                return false;
            }

            return true;
        }

        if (data === false) {
            return false;
        }

        if (data === true) {
            return true;
        }

        if (typeof data === 'string') {
            return true;
        }

        if (data === null) {
            return false;
        }

        action = action || null;

        if (action === null) {
            return true;
        }

        if (!(action in data)) {
            return false;
        }

        const value = data[action];

        if (value === 'all') {
            return true;
        }

        if (value === 'yes') {
            return true;
        }

        if (value === 'no') {
            return false;
        }

        if (isOwner === undefined) {
            return true;
        }

        if (isOwner) {
            if (value === 'own' || value === 'team') {
                return true;
            }
        }

        if (isShared) {
            return true;
        }

        if (inTeam) {
            if (value === 'team') {
                return true;
            }
        }

        let result = false;

        if (value === 'team') {
            if (inTeam === null && precise) {
                result = null;
            }
        }

        if (isOwner === null && precise) {
            result = null;
        }

        if (isShared === null) {
            result = null;
        }

        return result;
    }

    /**
     * Check access to model (entity).
     *
     * @param {module:model} model A model.
     * @param {Object.<string, string>|string|null} data Access data.
     * @param {module:acl-manager~action|null} [action=null] Action to check.
     * @param {boolean} [precise=false] To return `null` if not enough data is set in a model.
     *   E.g. the `teams` field is not yet loaded.
     * @returns {boolean|null} True if access allowed, null if not enough data to determine.
     */
    checkModel(model, data, action, precise) {
        if (this.getUser().isAdmin()) {
            return true;
        }

        let isShared = false;

        if (action === 'read' || action === 'stream') {
            isShared = this.checkIsShared(model);
        }

        const entityAccessData = {
            isOwner: this.checkIsOwner(model),
            inTeam: this.checkInTeam(model),
            isShared: isShared,
        };

        return this.checkScope(data, action, precise, entityAccessData);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * Check `delete` access to model.
     *
     * @param {module:model} model A model.
     * @param {Object.<string, string>|string|null} data Access data.
     * @param {boolean} [precise=false] To return `null` if not enough data is set in a model.
     *   E.g. the `teams` field is not yet loaded.
     * @returns {boolean} True if access allowed.
     */
    checkModelDelete(model, data, precise) {
        const result = this.checkModel(model, data, 'delete', precise);

        if (result) {
            return true;
        }

        if (data === false) {
            return false;
        }

        const d = data || {};

        if (d.read === 'no') {
            return false;
        }

        if (
            model.has('createdById') &&
            model.get('createdById') === this.getUser().id &&
            this.aclAllowDeleteCreated
        ) {
            if (!model.has('assignedUserId')) {
                return true;
            }

            if (!model.get('assignedUserId')) {
                return true;
            }

            if (model.get('assignedUserId') === this.getUser().id) {
                return true;
            }
        }

        return result;
    }

    /**
     * Check if a user is owner to a model.
     *
     * @param {module:model} model A model.
     * @returns {boolean|null} True if owner. Null if not clear.
     */
    checkIsOwner(model) {
        let result = false;

        if (model.hasField('assignedUser')) {
            if (this.getUser().id === model.get('assignedUserId')) {
                return true;
            }

            if (!model.has('assignedUserId')) {
                result = null;
            }
        }
        else if (model.hasField('createdBy')) {
            if (this.getUser().id === model.get('createdById')) {
                return true;
            }

            if (!model.has('createdById')) {
                result = null;
            }
        }

        if (model.hasField('assignedUsers')) {
            if (!model.has('assignedUsersIds')) {
                return null;
            }

            if ((model.get('assignedUsersIds') || []).includes(this.getUser().id)) {
                return true;
            }

            result = false;
        }

        return result;
    }

    /**
     * Check if a user in a team of a model.
     *
     * @param {module:model} model A model.
     * @returns {boolean|null} True if in a team. Null if not enough data to determine.
     */
    checkInTeam(model) {
        const userTeamIdList = this.getUser().getTeamIdList();

        if (!model.has('teamsIds')) {
            if (this.teamsFieldIsForbidden) {
                return true;
            }

            if (!model.hasField('teams')) {
                return false;
            }

            return null;
        }

        const teamIdList = model.getTeamIdList();

        let inTeam = false;

        userTeamIdList.forEach(id => {
            if (teamIdList.includes(id)) {
                inTeam = true;
            }
        });

        return inTeam;
    }

    /**
     * Check if a record is shared with the user.
     *
     * @param {module:model} model A model.
     * @returns {boolean|null} True if shared. Null if not enough data to determine.
     */
    checkIsShared(model) {
        if (!model.has('collaboratorsIds')) {
            if (this.collaboratorsFieldIsForbidden) {
                return true;
            }

            if (!model.hasField('collaborators')) {
                return false;
            }

            return null;
        }

        const collaboratorsIds = model.getLinkMultipleIdList('collaborators');

        return collaboratorsIds.includes(this.user.id);
    }

    /**
     * Get a permission level.
     *
     * @protected
     * @param {string} permission A permission name.
     * @returns {'yes'|'all'|'team'|'no'}
     */
    getPermissionLevel(permission) {
        return this._aclManager.getPermissionLevel(permission);
    }
}

Acl.extend = BullView.extend;

export default Acl;

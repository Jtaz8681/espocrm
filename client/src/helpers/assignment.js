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

import {inject} from 'di';
import Metadata from 'metadata';

/**
 * @since 10.0.0
 */
export default class AssignmentHelper {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @param {string} entityType
     * @return {boolean}
     */
    hasAssignedUserField(entityType) {
        return this.metadata.get(`entityDefs.${entityType}.fields.assignedUser.type`) === 'link' &&
            !this.metadata.get(`entityDefs.${entityType}.fields.assignedUser.disabled`) &&
            this.metadata.get(`entityDefs.${entityType}.links.assignedUser.entity`) === 'User';
    }

    /**
     * @param {string} entityType
     * @return {boolean}
     */
    hasAssignedUsersField(entityType) {
        return this.metadata.get(`entityDefs.${entityType}.fields.assignedUsers.type`) === 'linkMultiple' &&
            this.metadata.get(`entityDefs.${entityType}.links.assignedUsers.entity`) === 'User';
    }

    /**
     * @param {string} entityType
     * @return {boolean}
     */
    hasCollaboratorsField(entityType) {
        if (!this.metadata.get(`scopes.${entityType}.collaborators`)) {
            return false;
        }

        return this.metadata.get(`entityDefs.${entityType}.fields.collaborators.type`) === 'linkMultiple' &&
            this.metadata.get(`entityDefs.${entityType}.links.collaborators.entity`) === 'User';
    }
}

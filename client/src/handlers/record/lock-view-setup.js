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
import AssignmentHelper from 'helpers/assignment';

// noinspection JSUnusedGlobalSymbols
export default class LockViewSetup {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @private
     * @type {import('model').default}
     */
    model

    /**
     * @private
     * @type {import('views/record/detail').default}
     */
    view

    ignoreFieldList = [
        'isLocked',
        'modifiedAt',
        'modifiedBy',
        'streamUpdatedAt',
    ]

    /**
     * @param {import('views/record/detail').default} view
     */
    constructor(view) {
        this.view = view;
        this.model = view.model;
    }

    process() {
        const entityType = this.model.entityType;

        if (this.metadata.get(`scopes.${entityType}.lockable`) !== true) {
            return;
        }

        let wasLocked = false;
        const lockedMap = {};

        const ignoreFieldList = this.getIgnoreFieldList();

        const fieldsDefs = /** @type {Record.<string, Record>} */
            this.metadata.get(`entityDefs.${entityType}.fields`) ?? {};

        const lockableFields = Object.keys(fieldsDefs)
            .filter(field => {
                if (ignoreFieldList.includes(field)) {
                    return false;
                }

                const defs = fieldsDefs[field];

                return !defs.notLockable &&
                    !defs.readOnly &&
                    !defs.readOnlyAfterCreate;
            });

        const controlLocked = () => {
            const isLocked = this.model.attributes.isLocked;

            if (!isLocked && !wasLocked) {
                return;
            }

            if (isLocked) {
                wasLocked = true;
            }

            lockableFields.forEach(field => {
                if (isLocked) {
                    if (
                        this.view.recordHelper &&
                        this.view.recordHelper.getFieldStateParam(field, 'readOnly')
                    ) {
                        return;
                    }

                    lockedMap[field] = true;

                    this.view.setFieldReadOnly(field);

                    return;
                }

                if (!lockedMap[field]) {
                    return;
                }

                delete lockedMap[field];

                this.view.setFieldNotReadOnly(field);
            });
        }

        controlLocked();
        this.view.listenTo(this.model, 'change:isLocked', () => controlLocked());
    }

    /**
     * @private
     * @return {string[]}
     */
    getIgnoreFieldList() {
        const entityType = this.model.entityType;

        const ignoreFieldList = [...this.ignoreFieldList];

        const helper = new AssignmentHelper;

        if (helper.hasCollaboratorsField(entityType)) {
            if (helper.hasAssignedUsersField(entityType)) {
                if (this.model.getFieldParam('assignedUsers', 'notLockable')) {
                    ignoreFieldList.push('collaborators');
                }
            } else if (helper.hasAssignedUserField(entityType)) {
                if (this.model.getFieldParam('assignedUser', 'notLockable')) {
                    ignoreFieldList.push('collaborators');
                }
            }
        }
        return ignoreFieldList;
    }
}

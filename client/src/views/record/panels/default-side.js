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

import SidePanelView from 'views/record/panels/side';

/**
 * A default side panel.
 */
class DefaultSidePanelView extends SidePanelView {

    /**
     * @protected
     * @type {boolean}
     */
    complexCreatedDisabled

    /**
     * @protected
     * @type {boolean}
     */
    complexModifiedDisabled

    /**
     * @protected
     * @type {boolean}
     */
    hasIsLocked

    data() {
        const data = super.data();

        if (
            this.complexCreatedDisabled &&
            this.complexModifiedDisabled || (!this.hasComplexCreated && !this.hasComplexModified)
        ) {
            data.complexDateFieldsDisabled = true;
        }

        data.hasComplexCreated = this.hasComplexCreated;
        data.hasComplexModified = this.hasComplexModified;

        return data;
    }

    setup() {
        this.fieldList = Espo.Utils.cloneDeep(this.fieldList);

        const allFieldList = this.getFieldManager().getEntityTypeFieldList(this.model.entityType);

        this.hasComplexCreated =
            allFieldList.includes('createdAt') ||
            allFieldList.includes('createdBy');

        this.hasComplexModified =
            allFieldList.includes('modifiedAt') ||
            allFieldList.includes('modifiedBy');

        this.hasIsLocked = this.getMetadata().get(`scopes.${this.model.entityType}.lockable`) === true;

        super.setup();
    }

    setupFields() {
        super.setupFields();

        if (this.hasIsLocked) {
            this.fieldList.push({
                name: 'isLocked',
                view: 'views/global/fields/is-locked',
            });

            this.controlIsLockedField();
            this.listenTo(this.model, 'change:isLocked', () => this.controlIsLockedField());
        }

        if (!this.complexCreatedDisabled) {
            if (this.hasComplexCreated) {
                this.fieldList.push({
                    name: 'complexCreated',
                    labelText: this.translate('Created'),
                    isAdditional: true,
                    view: 'views/fields/complex-created',
                    readOnly: true,
                });

                if (!this.model.get('createdById') && !this.model.get('createdAt')) {
                    this.recordViewObject.hideField('complexCreated');
                }
            }
        } else {
            this.recordViewObject.hideField('complexCreated');
        }

        if (!this.complexModifiedDisabled) {
            if (this.hasComplexModified) {
                this.fieldList.push({
                    name: 'complexModified',
                    labelText: this.translate('Modified'),
                    isAdditional: true,
                    view: 'views/fields/complex-created',
                    readOnly: true,
                    options: {
                        baseName: 'modified',
                    },
                });

                if (!this.isModifiedVisible()) {
                    this.recordViewObject.hideField('complexModified');
                }
            }
        } else {
            this.recordViewObject.hideField('complexModified');
        }

        if (!this.complexCreatedDisabled && this.hasComplexCreated) {
            this.listenTo(this.model, 'change', () => {
                if (!this.model.hasChanged('createdById') && !this.model.hasChanged('createdAt')) {
                    return;
                }

                if (!this.model.get('createdById') && !this.model.get('createdAt')) {
                    return;
                }

                this.recordViewObject.showField('complexCreated');
            });
        }

        if (!this.complexModifiedDisabled && this.hasComplexModified) {
            this.listenTo(this.model, 'change', () => {
                if (!this.model.hasChanged('modifiedById') && !this.model.hasChanged('modifiedAt')) {
                    return;
                }

                if (!this.isModifiedVisible()) {
                    return;
                }

                this.recordViewObject.showField('complexModified');
            });
        }

        if (
            this.getMetadata().get(['scopes', this.model.entityType ,'stream']) &&
            !this.getUser().isPortal()
        ) {
            this.fieldList.push({
                name: 'followers',
                labelText: this.translate('Followers'),
                isAdditional: true,
                view: 'views/fields/followers',
                readOnly: true,
            });

            this.controlFollowersField();

            this.listenTo(this.model, 'change:followersIds', () => this.controlFollowersField());
        }
    }

    /**
     * @private
     * @return {boolean}
     */
    isModifiedVisible() {
        if (!this.hasComplexModified) {
            return false;
        }

        if (!this.model.get('modifiedById') && !this.model.get('modifiedAt')) {
            return false;
        }

        if (!this.model.get('modifiedById') && this.model.get('modifiedAt') === this.model.get('createdAt')) {
            return false;
        }

        return true;
    }

    controlFollowersField() {
        if (this.model.get('followersIds') && this.model.get('followersIds').length) {
            this.recordViewObject.showField('followers');

            return;
        }

        this.recordViewObject.hideField('followers');
    }

    /**
     * @private
     */
    controlIsLockedField() {
        if (this.model.attributes.isLocked) {
            this.recordViewObject.showField('isLocked');
        } else {
            this.recordViewObject.hideField('isLocked');
        }
    }
}

export default DefaultSidePanelView;

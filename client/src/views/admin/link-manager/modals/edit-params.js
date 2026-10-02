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

import ModalView from 'views/modal';
import Model from 'model';
import EditForModalRecordView from 'views/record/edit-for-modal';
import BoolFieldView from 'views/fields/bool';
import Ui from 'ui';
import Ajax from 'ajax';

export default class LinkManagerEditParamsModalView extends ModalView {

    templateContent = `
        <div class="record no-side-margin">{{{record}}}</div>
    `

    /**
     * @private
     * @type {string}
     */
    type

    /**
     * @private
     * @type {string|null}
     */
    foreignType = null

    /**
     * @private
     * @type {string|null}
     */
    foreignEntityType

    /**
     * @param {{
     *     entityType: string,
     *     link: string,
     * }} props
     */
    constructor(props) {
        super();

        this.props = props;
    }

    setup() {
        this.headerText = this.translate('Parameters', 'labels', 'EntityManager') + ' · ' +
            this.translate(this.props.entityType, 'scopeNames') + ' · ' +
            this.translate(this.props.link, 'links', this.props.entityType);

        this.systemEntityTypeList = ['User', 'Team'];

        /** @type {{type: string, isCustom?: boolean, entity?: string, foreign?: string|null}} */
        const defs = this.getMetadata().get(`entityDefs.${this.props.entityType}.links.${this.props.link}`) || {};
        this.type = defs.type;

        const foreignEntityType = defs.entity ?? null;
        const foreign = defs.foreign;

        this.foreignEntityType = foreignEntityType;

        if (foreignEntityType && foreign) {
            this.foreignType = this.getMetadata().get(`entityDefs.${foreignEntityType}.links.${foreign}.type`);
        }

        this.buttonList = [
            {
                name: 'save',
                style: 'danger',
                label: 'Save',
                onClick: () => this.save(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
                onClick: () => this.close(),
            },
        ];

        this.shortcutKeys = {
            'Control+Enter': (e) => {
                e.preventDefault();
                e.stopPropagation();

                this.save()
            },
            'Control+KeyS': (e) => {
                e.preventDefault();
                e.stopPropagation();

                this.save(true)
            },
        };

        if (!defs.isCustom) {
            this.addDropdownItem({
                name: 'resetToDefault',
                text: this.translate('Reset to Default', 'labels', 'Admin'),
                onClick: () => this.resetToDefault(),
            });
        }

        this.formModel = new Model(this.getParamsFromMetadata());

        this.recordView = new EditForModalRecordView({
            model: this.formModel,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new BoolFieldView({
                                    name: 'readOnly',
                                    labelText: this.translate('readOnly', 'fields', 'Admin'),
                                    params: {
                                        tooltip: 'EntityManager.linkParamReadOnly',
                                    },
                                }),
                            },
                            false
                        ],
                        [
                            {
                                view: new BoolFieldView({
                                    name: 'cascadeRemoval',
                                    labelText: this.translate('cascadeRemoval', 'fields', 'Admin'),
                                    params: {
                                        tooltip: 'EntityManager.linkParamCascadeRemoval',
                                    },
                                }),
                            },
                            false
                        ]
                    ]
                }
            ]
        });

        this.assignView('record', this.recordView).then(() => {
            if (!this.hasReadOnly()) {
                this.recordView.hideField('readOnly');
                this.recordView.setFieldReadOnly('readOnly');
            }

            if (!this.hasCascadeRemoval()) {
                this.recordView.hideField('cascadeRemoval');
                this.recordView.setFieldReadOnly('cascadeRemoval');
            }
        });
    }

    /**
     * @private
     * @return {boolean}
     */
    hasReadOnly() {
        return ['hasMany', 'hasChildren'].includes(this.type);
    }

    /**
     * @private
     * @return {boolean}
     */
    hasCascadeRemoval() {
        if (!this.foreignEntityType) {
            return false;
        }

        if (
            !this.getMetadata().get(`scopes.${this.foreignEntityType}.object`) ||
            this.systemEntityTypeList.includes(this.foreignEntityType)
        ) {
            return false;
        }

        return ['hasChildren', 'hasOne'].includes(this.type) ||
            (this.type === 'belongsTo' && this.foreignType === 'hasOne') ||
            (this.type === 'hasMany' && this.foreignType === 'belongsTo');
    }

    /**
     * @private
     * @return {Record}
     */
    getParamsFromMetadata() {
        /** @type {Record} */
        const defs = this.getMetadata().get(`entityDefs.${this.props.entityType}.links.${this.props.link}`) || {};

        return {
            readOnly: defs.readOnly ?? false,
            cascadeRemoval: defs.cascadeRemoval ?? false,
        };
    }

    /**
     * @private
     */
    disableAllActionItems() {
        this.disableButton('save');
        this.hideActionItem('resetToDefault');
    }

    /**
     * @private
     */
    enableAllActionItems() {
        this.enableButton('save');
        this.showActionItem('resetToDefault');
    }

    /**
     * @private
     */
    async save(noClose = false) {
        if (this.recordView.validate()) {
            return;
        }

        this.disableAllActionItems();

        Ui.notifyWait();

        const params = {};

        if (this.hasReadOnly()) {
            params.readOnly = this.formModel.attributes.readOnly;
        }

        if (this.hasCascadeRemoval()) {
            params.cascadeRemoval = this.formModel.attributes.cascadeRemoval;
        }

        try {
            await Espo.Ajax.postRequest('EntityManager/action/updateLinkParams', {
                entityType: this.props.entityType,
                link: this.props.link,
                params: params,
            });
        } catch (e) {
            this.enableAllActionItems();

            return;
        }

        await Promise.all([this.getMetadata().loadSkipCache()]);
        this.broadcastUpdate();

        if (!noClose) {
            this.close();
        } else {
            this.enableAllActionItems();
        }

        Ui.success(this.translate('Saved'));
    }

    /**
     * @private
     */
    async resetToDefault() {
        this.disableAllActionItems();
        Ui.notifyWait();

        try {
            await Ajax.postRequest('EntityManager/action/resetLinkParamsToDefault', {
                entityType: this.props.entityType,
                link: this.props.link,
            });
        } catch (e) {
            this.enableAllActionItems();

            return;
        }

        await Promise.all([this.getMetadata().loadSkipCache()]);
        this.broadcastUpdate();
        this.formModel.setMultiple(this.getParamsFromMetadata());
        this.enableAllActionItems();

        Ui.success(this.translate('Saved'));
    }

    /**
     * @private
     */
    broadcastUpdate() {
        this.getHelper().broadcastChannel.postMessage('update:metadata');
    }
}

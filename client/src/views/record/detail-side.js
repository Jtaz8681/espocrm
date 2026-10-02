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

/** @module views/record/detail-side */

import PanelsContainerRecordView from 'views/record/panels-container';

/**
 * A detail-side record view.
 */
class DetailSideRecordView extends PanelsContainerRecordView {

    /** @inheritDoc */
    template = 'record/side'

    /** @inheritDoc */
    mode = 'detail'
    readOnly = false
    inlineEditDisabled = false
    name = 'side'
    defaultPanel = true

    /**
     * A panel list.
     *
     * @protected
     * @type {module:views/record/panels-container~panel[]}
     */
    panelList = []

    /**
     * A default panel.
     *
     * @type {module:views/record/panels-container~panel}
     */
    defaultPanelDefs = {
        name: 'default',
        label: false,
        view: 'views/record/panels/default-side',
        isForm: true,
        options: {
            fieldList: [
                {
                    name: ':assignedUser'
                },
                {
                    name: 'teams'
                },
            ],
        }
    }

    init() {
        this.panelList = this.options.panelList || this.panelList;
        this.scope = this.entityType = this.options.model.entityType;

        this.recordHelper = this.options.recordHelper;

        this.panelList = Espo.Utils.clone(this.panelList);

        this.readOnlyLocked = this.options.readOnlyLocked || this.readOnly;
        this.readOnly = this.options.readOnly || this.readOnly;
        this.inlineEditDisabled = this.options.inlineEditDisabled || this.inlineEditDisabled;

        this.recordViewObject = this.options.recordViewObject;
    }

    /** @inheritDoc */
    setupPanels() {}

    setup() {
        this.type = this.mode;

        if ('type' in this.options) {
            this.type = this.options.type;
        }

        this.setupInitial();

        this.setupPanels();

        if (!this.additionalPanelsDisabled) {
            const additionalPanels = this.getMetadata()
                .get(['clientDefs', this.scope, 'sidePanels', this.type]) || [];

            additionalPanels.forEach((panel) => {
                this.panelList.push(panel);
            });
        }

        this.panelList = this.panelList.map(item => {
            if ('reference' in item && item.reference) {
                // noinspection UnnecessaryLocalVariableJS
                /** @type {module:views/record/panels-container~panel} */
                const newItem = {
                    ...this.getMetadata().get(`app.clientRecord.panels.${item.reference}`),
                    ...item,
                };

                return newItem;
            }

            return item;
        });

        this.panelList = this.panelList.filter((p) => {
            if (p.aclScope) {
                if (!this.getAcl().checkScope(p.aclScope)) {
                    return;
                }
            }

            if (p.accessDataList) {
                if (!Espo.Utils.checkAccessDataList(p.accessDataList, this.getAcl(), this.getUser())) {
                    return false;
                }
            }

            return true;
        });

        this.panelList = this.panelList.map((p) => {
            const item = Espo.Utils.clone(p);

            if (this.recordHelper.getPanelStateParam(p.name, 'hidden') !== null) {
                item.hidden = this.recordHelper.getPanelStateParam(p.name, 'hidden');
            } else {
                this.recordHelper.setPanelStateParam(p.name, 'hidden', item.hidden || false);
            }

            return item;
        });

        this.panelList.forEach((item) => {
            item.actionsViewKey = item.name + 'Actions';
        });

        this.wait(
            Promise.all([
                new Promise((resolve) => {
                    this.getHelper().layoutManager.get(
                        this.scope,
                        'sidePanels' + Espo.Utils.upperCaseFirst(this.type),
                        (layoutData) => {
                            this.layoutData = layoutData;

                            resolve();
                        });
                }),
                new Promise((resolve) => {
                    if (
                        !this.defaultPanel ||
                        this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanelDisabled']) ||
                        this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanel', this.type]) ||
                        this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanelFieldLists', this.type]) ||
                        this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanelFieldList'])
                    ) {
                        resolve();

                        return;
                    }

                    this.getHelper()
                        .layoutManager
                        .get(this.scope, 'defaultSidePanel', (layoutData) => {
                            this.defaultSidePanelLayoutData = layoutData;

                            resolve();
                        });
                }),
            ]).then(() => {
                if (this.defaultPanel) {
                    this.setupDefaultPanel();
                }

                this.alterPanels();
                this.setupPanelsFinal();
                this.setupPanelViews();
            })
        );
    }

    /**
     * Set up a default panel.
     *
     * @protected
     */
    setupDefaultPanel() {
        let met = false;

        this.panelList.forEach((item) => {
            if (item.name === 'default') {
                met = true;
            }
        });

        if (met) {
            return;
        }

        let defaultPanelDefs = this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanel', this.type]);

        if (defaultPanelDefs === false) {
            return;
        }

        if (this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanelDisabled'])) {
            return;
        }

        defaultPanelDefs = defaultPanelDefs || this.defaultPanelDefs;

        if (!defaultPanelDefs) {
            return;
        }

        defaultPanelDefs = Espo.Utils.cloneDeep(defaultPanelDefs);

        defaultPanelDefs.view = this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanelView']) ||
            defaultPanelDefs.view;

        let fieldList = this.getMetadata()
            .get(['clientDefs', this.scope, 'defaultSidePanelFieldLists', this.type]);

        if (!fieldList) {
            fieldList = this.getMetadata().get(['clientDefs', this.scope, 'defaultSidePanelFieldList']);
        }

        if (!fieldList && this.defaultSidePanelLayoutData) {
            fieldList = this.defaultSidePanelLayoutData;
        }

        if (fieldList) {
            defaultPanelDefs.options = defaultPanelDefs.options || {};
            defaultPanelDefs.options.fieldList = fieldList;
        }

        fieldList = defaultPanelDefs.options.fieldList;

        if (fieldList && fieldList.length) {
            fieldList.forEach((item, i) => {
                if (typeof item !== 'object') {
                    item = {name: item};

                    fieldList[i] = item;
                }

                if (item.name === ':assignedUser') {
                    if (this.model.hasField('assignedUsers')) {
                        item.name = 'assignedUsers';

                        if (!this.model.getFieldParam('assignedUsers', 'view')) {
                            item.view = 'views/fields/assigned-users';
                        }
                    }
                    else if (this.model.hasField('assignedUser')) {
                        item.name = 'assignedUser';
                    }
                    else {
                        defaultPanelDefs.options.fieldList[i] = {};
                    }
                }
            });

            const fieldDefs = this.getMetadata().get(['entityDefs', this.entityType, 'fields']) || {};

            defaultPanelDefs.options.fieldList = fieldList.filter(item => {
                const defs = fieldDefs[item.name] || {}

                return !defs.disabled;
            });
        }

        this.panelList.unshift(defaultPanelDefs);
    }
}

export default DetailSideRecordView;

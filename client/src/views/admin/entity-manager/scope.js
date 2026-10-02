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

import View from 'view';
import DetailRecordView from 'views/record/detail';
import Model from 'model';
import EntityManagerPrimaryFiltersFieldView from 'views/admin/entity-manager/fields/primary-filters';

class EntityManagerScopeView extends View {

    template = 'admin/entity-manager/scope'

    scope

    data() {
        return {
            scope: this.scope,
            isEditable: this.isEditable,
            isRemovable: this.isRemovable,
            isCustomizable: this.isCustomizable,
            type: this.type,
            hasLayouts: this.hasLayouts,
            label: this.label,
            hasFormula: this.hasFormula,
            hasFields: this.hasFields,
            hasRelationships: this.hasRelationships,
        };
    }

    events = {
        /** @this EntityManagerScopeView */
        'click [data-action="editEntity"]': function () {
            this.getRouter().navigate(`#Admin/entityManager/edit&scope=${this.scope}`, {trigger: true});
        },
        /** @this EntityManagerScopeView */
        'click [data-action="removeEntity"]': function () {
            this.removeEntity();
        },
        /** @this EntityManagerScopeView */
        'click [data-action="editFormula"]': function () {
            this.editFormula();
        },
    }

    setup() {
        this.scope = this.options.scope;

        this.setupScopeData();

        this.model = new Model({
            name: this.scope,
            type: this.type,
            label: this.label,
            primaryFilters: this.getPrimaryFilters(),
        });

        this.model.setDefs({
            fields: {
                name: {
                    type: 'varchar',
                },
                type: {
                    type: 'varchar',
                },
                label: {
                    type: 'varchar',
                },
                primaryFilters: {
                    type: 'array',
                },
            }
        });

        this.recordView = new DetailRecordView({
            model: this.model,
            inlineEditDisabled: true,
            buttonsDisabled: true,
            readOnly: true,
            detailLayout: [
                {
                    tabBreak: true,
                    tabLabel: this.translate('General', 'labels', 'Settings'),
                    rows: [
                        [
                            {
                                name: 'name',
                                labelText: this.translate('name', 'fields', 'EntityManager'),
                            },
                            {
                                name: 'type',
                                labelText: this.translate('type', 'fields', 'EntityManager'),
                            }
                        ],
                        [
                            {
                                name: 'label',
                                labelText: this.translate('label', 'fields', 'EntityManager'),
                            },
                            false
                        ]
                    ]
                },
                {
                    tabBreak: true,
                    tabLabel: this.translate('Details'),
                    rows: [
                        [
                            {
                                view: new EntityManagerPrimaryFiltersFieldView({
                                    name: 'primaryFilters',
                                    labelText: this.translate('primaryFilters', 'fields', 'EntityManager'),
                                    targetEntityType: this.scope,
                                }),
                            },
                            false
                        ]
                    ]
                }
            ],
        });

        this.assignView('record', this.recordView, '.record-container');

        if (!this.type) {
            this.recordView.hideField('type');
        }
    }

    setupScopeData() {
        const scopeData = /** @type {Record} */this.getMetadata().get(['scopes', this.scope]);
        const entityManagerData = this.getMetadata().get(['scopes', this.scope, 'entityManager']) || {};

        if (!scopeData) {
            throw new Espo.Exceptions.NotFound();
        }

        this.isRemovable = !!scopeData.isCustom;

        if (scopeData.isNotRemovable) {
            this.isRemovable = false;
        }

        this.isCustomizable = !!scopeData.customizable;
        this.type = scopeData.type;
        this.isEditable = true;
        this.hasLayouts = scopeData.layouts;
        this.hasFormula = this.isCustomizable;
        this.hasFields = this.isCustomizable;
        this.hasRelationships = this.isCustomizable;

        if (!scopeData.customizable) {
            this.isEditable = false;
        }

        if ('edit' in entityManagerData) {
            this.isEditable = entityManagerData.edit;
        }

        if ('layouts' in entityManagerData) {
            this.hasLayouts = entityManagerData.layouts;
        }

        if ('formula' in entityManagerData) {
            this.hasFormula = entityManagerData.formula;
        }

        if ('fields' in entityManagerData) {
            this.hasFields = entityManagerData.fields;
        }

        if ('relationships' in entityManagerData) {
            this.hasRelationships = entityManagerData.relationships;
        }

        this.label = this.getLanguage().translate(this.scope, 'scopeNames');
    }

    editFormula() {
        Espo.Ui.notifyWait();

        Espo.loader.requirePromise('views/admin/entity-manager/modals/select-formula')
            .then(View => {
                /** @type {module:views/modal} */
                const view = new View({
                    scope: this.scope,
                });

                this.assignView('dialog', view).then(() => {
                    Espo.Ui.notify(false);

                    view.render();
                });
            });
    }

    removeEntity() {
        const scope = this.scope;

        this.confirm(this.translate('confirmRemove', 'messages', 'EntityManager'), () => {
            Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

            this.disableButtons();

            Espo.Ajax.postRequest('EntityManager/action/removeEntity', {name: scope})
                .then(() => {
                    this.getMetadata()
                        .loadSkipCache()
                        .then(() => {
                            this.getConfig().load().then(() => {
                                Espo.Ui.notify(false);

                                this.broadcastUpdate();
                                this.getRouter().navigate('#Admin/entityManager', {trigger: true});
                            });
                        });
                })
                .catch(() => this.enableButtons());
        });
    }

    updatePageTitle() {
        this.setPageTitle(
            this.getLanguage().translate('Entity Manager', 'labels', 'Admin')
        );
    }

    disableButtons() {
        this.$el.find('.btn.action').addClass('disabled').attr('disabled', 'disabled');
        this.$el.find('.item-dropdown-button').addClass('disabled').attr('disabled', 'disabled');
    }

    enableButtons() {
        this.$el.find('.btn.action').removeClass('disabled').removeAttr('disabled');
        this.$el.find('.item-dropdown-button"]').removeClass('disabled').removeAttr('disabled');
    }

    broadcastUpdate() {
        this.getHelper().broadcastChannel.postMessage('update:metadata');
        this.getHelper().broadcastChannel.postMessage('update:settings');
    }

    /**
     * @return {string[]}
     */
    getPrimaryFilters() {
        const list = this.getMetadata().get(`clientDefs.${this.scope}.filterList`, []).map(item => {
            if (typeof item === 'object' && item.name) {
                return item.name;
            }

            return item.toString();
        });

        if (this.getMetadata().get(`scopes.${this.scope}.stars`)) {
            list.unshift('starred');
        }

        return list;
    }
}

export default EntityManagerScopeView;

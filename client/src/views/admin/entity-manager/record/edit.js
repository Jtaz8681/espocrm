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

import EditRecordView from 'views/record/edit';

export default class EntityManagerEditRecordView extends EditRecordView {

    bottomView = null
    sideView = null

    dropdownItemList = []

    accessControlDisabled = true
    saveAndContinueEditingAction = false
    saveAndNewAction = false

    shortcutKeys = {
        'Control+Enter': 'save',
        'Control+KeyS': 'save',
    }

    setup() {
        this.isCreate = this.options.isNew;

        this.scope = 'EntityManager';

        this.subjectEntityType = this.options.subjectEntityType;

        if (!this.isCreate) {
            this.buttonList = [
                {
                    name: 'save',
                    style: 'danger',
                    label: 'Save',
                    onClick: () => this.actionSave(),
                },
                {
                    name: 'cancel',
                    label: 'Cancel',
                },
            ];
        } else {
            this.buttonList = [
                {
                    name: 'save',
                    style: 'danger',
                    label: 'Create',
                    onClick: () => this.actionSave(),
                },
                {
                    name: 'cancel',
                    label: 'Cancel',
                },
            ];
        }

        if (!this.isCreate && !this.options.isCustom) {
            this.buttonList.push({
                name: 'resetToDefault',
                text: this.translate('Reset to Default', 'labels', 'Admin'),
                onClick: () => this.actionResetToDefault(),
            });
        }

        super.setup();

        if (this.isCreate) {
            this.hideField('sortBy');
            this.hideField('sortDirection');
            this.hideField('textFilterFields');
            this.hideField('statusField');
            this.hideField('fullTextSearch');
            this.hideField('countDisabled');
            this.hideField('kanbanViewMode');
            this.hideField('kanbanStatusIgnoreList');
            this.hideField('disabled');
        }

        if (!this.options.hasColorField) {
            this.hideField('color');
        }

        if (!this.options.hasStreamField) {
            this.hideField('stream');
        }

        if (!this.isCreate) {
            this.manageKanbanFields({});

            this.listenTo(this.model, 'change:statusField', (m, v, o) => {
                this.manageKanbanFields(o);
            });

            this.manageKanbanViewModeField();

            this.listenTo(this.model, 'change:kanbanViewMode', () => {
                this.manageKanbanViewModeField();
            });
        }
    }

    actionSave(data) {
        this.trigger('save');
    }

    actionCancel() {
        this.trigger('cancel');
    }

    actionResetToDefault() {
        this.trigger('reset-to-default');
    }

    manageKanbanViewModeField() {
        if (this.model.get('kanbanViewMode')) {
            this.showField('kanbanStatusIgnoreList');
        } else {
            this.hideField('kanbanStatusIgnoreList');
        }
    }

    manageKanbanFields(o) {
        if (o.ui) {
            this.model.set('kanbanStatusIgnoreList', []);
        }

        if (this.model.get('statusField')) {
            this.setKanbanStatusIgnoreListOptions();

            this.showField('kanbanViewMode');

            if (this.model.get('kanbanViewMode')) {
                this.showField('kanbanStatusIgnoreList');
            } else {
                this.hideField('kanbanStatusIgnoreList');
            }
        } else {
            this.hideField('kanbanViewMode');
            this.hideField('kanbanStatusIgnoreList');
        }
    }

    setKanbanStatusIgnoreListOptions() {
        const statusField = this.model.get('statusField');

        let optionList = this.getMetadata()
            .get(`entityDefs.${this.subjectEntityType}.fields.${statusField}.options`) ?? [];

        const optionsReference = this.getMetadata()
            .get(`entityDefs.${this.subjectEntityType}.fields.${statusField}.optionsReference`);

        if (optionsReference) {
            const [entityType, field] = optionsReference.split('.');

            optionList = this.getMetadata()
                .get(`entityDefs.${entityType}.fields.${field}.options`) ?? [];
        }

        this.setFieldOptionList('kanbanStatusIgnoreList', optionList);

        const fieldView = this.getFieldView('kanbanStatusIgnoreList');

        if (!fieldView) {
            this.once('after:render', () => this.setKanbanStatusIgnoreListTranslation());

            return;
        }

        this.setKanbanStatusIgnoreListTranslation();
    }

    setKanbanStatusIgnoreListTranslation() {
        /** @type {import('views/fields/multi-enum').default} */
        const fieldView = this.getFieldView('kanbanStatusIgnoreList');

        const statusField = this.model.get('statusField');

        fieldView.params.translation =
            this.getMetadata().get(['entityDefs', this.subjectEntityType, 'fields', statusField, 'translation']) ||
            `${this.subjectEntityType}.options.${statusField}`;

        fieldView.setupTranslation();
    }
}

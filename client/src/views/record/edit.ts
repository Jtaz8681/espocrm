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

import DetailRecordView, {
    Button,
    DetailRecordViewOptions,
    DetailRecordViewSchema,
    DropdownItem,
} from 'views/record/detail';
import {SaveOptions} from 'views/record/base';
import Ui from 'ui';

export interface EditRecordViewSchema extends DetailRecordViewSchema {
    options: EditRecordViewOptions;
}

export interface EditRecordViewOptions extends DetailRecordViewOptions {
    /**
     * A duplicate record source ID.
     */
    duplicateSourceId?: string;

    /**
     * Fields to highlight after render.
     */
    highlightFieldList?: string[];
}

/**
 * An edit-record view. Used for create and edit.
 */
class EditRecordView<S extends EditRecordViewSchema = EditRecordViewSchema> extends DetailRecordView<S> {

    protected template: string = 'record/edit'

    protected type: string = 'edit'

    protected override fieldsMode: DetailRecordView['fieldsMode'] = 'edit'

    override mode: DetailRecordView['mode'] = 'edit'

    protected buttonList: Button[] = [
        {
            name: 'save',
            label: 'Save',
            style: 'primary',
            title: 'Ctrl+Enter',
        },
        {
            name: 'cancel',
            label: 'Cancel',
            title: 'Esc',
        },
    ]

    protected dropdownItemList: DropdownItem[] = []

    protected sideView: DetailRecordView['sideView'] = 'views/record/edit-side'

    protected bottomView: DetailRecordView['bottomView'] = 'views/record/edit-bottom'

    protected duplicateAction: boolean = false

    protected saveAndContinueEditingAction: boolean = true

    protected saveAndNewAction: boolean = true

    protected setupHandlerType: string = 'record/edit'

    protected async actionSave(data?: {options?: SaveOptions}): Promise<void> {
        data = data || {};

        const isNew = this.isNew;

        try {
            await this.save(data.options);
        } catch (reason) {
            return await Promise.reject(reason);
        }

        if (this.options.duplicateSourceId) {
            this.returnUrl = null;
        }

        this.exit(isNew ? 'create' : 'save');
    }

    /**
     * A `cancel` action.
     */
    protected actionCancel() {
        this.cancel();
    }

    /**
     * Cancel.
     */
    protected cancel() {
        if (this.isChanged) {
            this.resetModelChanges();
        }

        this.setIsNotChanged();
        this.exit('cancel');
    }

    protected setupBeforeFinal() {
        let promise: Promise<any> | undefined = undefined;

        if (this.model.isNew()) {
            promise = this.populateDefaults();
        }

        if (!promise) {
            // Attributes are yet not ready.
            super.setupBeforeFinal();
        }

        // @todo To be removed.
        if (promise) {
            this.wait(promise);

            promise.then(() => {
                super.setupBeforeFinal();

                this.processDynamicLogic();
            });
        }

        if (this.model.isNew()) {
            this.once('after:render', () => {
                this.model.setMultiple(this.fetch(), {silent: true});
            })
        }

        if (this.options.focusForCreate) {
            this.once('after:render', () => {
                if (this.$el.closest('.modal').length) {
                    setTimeout(() => this.focusForCreate(), 50);

                    return;
                }

                this.focusForCreate();
            });
        }

        this.setupHighlight();
    }

    protected setupActionItems() {
        super.setupActionItems();

        if (
            this.saveAndContinueEditingAction &&
            this.entityType &&
            this.getAcl().checkScope(this.entityType, 'edit')
        ) {
            this.dropdownItemList.push({
                name: 'saveAndContinueEditing',
                label: 'Save & Continue Editing',
                title: 'Ctrl+S',
                groupIndex: 0,
                iconClass: 'far fa-floppy-disk',
            });
        }

        if (
            this.isNew &&
            this.saveAndNewAction &&
            this.entityType &&
            this.getAcl().checkScope(this.entityType, 'create')
        ) {
            this.dropdownItemList.push({
                name: 'saveAndNew',
                label: 'Save & New',
                title: 'Ctrl+Alt+Enter',
                groupIndex: 0,
            });
        }
    }

    /**
     * A `save-and-create-new` action.
     */
    protected actionSaveAndNew(data?: {focusForCreate?: boolean, options?: SaveOptions}) {
        data = data || {};

        const proceedCallback = () => {
            Ui.success(this.translate('Created'));

            this.getRouter().dispatch(this.scope, 'create', {
                rootUrl: this.options.rootUrl,
                focusForCreate: !!data.focusForCreate,
            });

            this.getRouter().navigate(`#${this.scope}/create`, {trigger: false});
        };

        this.save(data.options)
            .then(proceedCallback)
            .catch(() => {});

        if (this.lastSaveCancelReason === 'notModified') {
             proceedCallback();
        }
    }

    protected handleShortcutKeyEscape(event: KeyboardEvent) {
        if (this.buttonsDisabled) {
            return;
        }

        if (this.buttonList.findIndex(item => item.name === 'cancel' && !item.hidden && !item.disabled) === -1) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const focusedFieldView = this.getFocusedFieldView();

        if (focusedFieldView) {
            this.model.setMultiple(focusedFieldView.fetch());
        }

        if (this.isChanged) {
            this.confirm({message: this.translate('confirmLeaveOutMessage', 'messages')})
                .then(() => this.actionCancel());

            return;
        }

        this.actionCancel();
    }

    protected handleShortcutKeyCtrlAltEnter(event: KeyboardEvent) {
        if (this.buttonsDisabled) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (!this.saveAndNewAction) {
            return;
        }

        if (!this.hasAvailableActionItem('saveAndNew')) {
            return;
        }

        this.actionSaveAndNew({focusForCreate: true});
    }

    private setupHighlight() {
        if (!this.options.highlightFieldList) {
            return;
        }

        this.on('after:render', () => {
            const fieldList = this.options.highlightFieldList!;

            fieldList
                .map(it => this.getFieldView(it))
                .filter(view => view != null)
                .forEach(view => view.highlight());
        });
    }
}

export default EditRecordView;

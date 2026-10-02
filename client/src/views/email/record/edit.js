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

/** @module views/email/record/edit */

import EditRecordView from 'views/record/edit';
import EmailDetailRecordView from 'views/email/record/detail';

class EmailEditRecordView extends EditRecordView {

    init() {
        super.init();

        EmailDetailRecordView.prototype.layoutNameConfigure.call(this);
    }

    setup() {
        super.setup();

        if (['Archived', 'Sent'].includes(this.model.get('status'))) {
            this.shortcutKeyCtrlEnterAction = 'save';
        }

        this.addDropdownItem({
            name: 'send',
            label: 'Send',
            onClick: () => this.actionSend(),
        });

        this.controlSendButton();

        if (this.model.get('status') === 'Draft') {
            this.setFieldReadOnly('dateSent');

            // Not implemented for detail view yet.
            this.hideField('selectTemplate');
        }

        this.handleAttachmentField();
        this.handleCcField();
        this.handleBccField();

        this.listenTo(this.model, 'change:attachmentsIds', () => this.handleAttachmentField());
        this.listenTo(this.model, 'change:cc', () => this.handleCcField());
        this.listenTo(this.model, 'change:bcc', () => this.handleBccField());
    }

    handleAttachmentField() {
        if (
            (this.model.get('attachmentsIds') || []).length === 0 &&
            !this.isNew &&
            this.model.get('status') !== 'Draft'
        ) {
            this.hideField('attachments');

            return;
        }

        this.showField('attachments');
    }

    handleCcField() {
        if (!this.model.get('cc') && this.model.get('status') !== 'Draft') {
            this.hideField('cc');
        } else {
            this.showField('cc');
        }
    }

    handleBccField() {
        if (!this.model.get('bcc') && this.model.get('status') !== 'Draft') {
            this.hideField('bcc');
        } else {
            this.showField('bcc');
        }
    }

    controlSendButton()  {
        const status = this.model.get('status');

        if (status === 'Draft') {
            this.showActionItem('send');

            return;
        }

        this.hideActionItem('send');
    }

    // noinspection JSUnusedGlobalSymbols
    actionSaveDraft() {
        this.actionSaveAndContinueEditing();
    }

    // noinspection JSUnusedGlobalSymbols
    actionSend() {
        EmailDetailRecordView.prototype.send.call(this)
            .then(() => this.exit())
            .catch(() => {});
    }

    /**
     * @protected
     * @param {KeyboardEvent} e
     */
    handleShortcutKeyCtrlS(e) {
        if (this.inlineEditModeIsOn || this.buttonsDisabled) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        if (this.mode !== this.MODE_EDIT) {
            return;
        }

        if (!this.saveAndContinueEditingAction) {
            return;
        }

        if (!this.hasAvailableActionItem('saveAndContinueEditing')) {
            return;
        }

        this.actionSaveAndContinueEditing();
    }
}

export default EmailEditRecordView;

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

import DetailRecordView from 'views/record/detail';

export default class extends DetailRecordView {

    duplicateAction = true
    saveAndContinueEditingAction = true

    setup() {
        super.setup();

        this.listenToInsertField();

        this.hideField('insertField');

        this.on('before:set-edit-mode', () => this.showField('insertField'));
        this.on('before:set-detail-mode', () => this.hideField('insertField'));
    }

    listenToInsertField() {
        this.listenTo(this.model, 'insert-field', /** {entityType: string, field: string} */o => {
            const tag = `{${o.entityType}.${o.field}}`;

            const bodyView = /** @type {import('views/fields/wysiwyg').default} */
                this.getFieldView('body');

            if (!bodyView) {
                return;
            }

            if (this.model.attributes.isHtml) {
                const $anchor = $(window.getSelection().anchorNode);

                if (!$anchor.closest('.note-editing-area').length) {
                    return;
                }

                bodyView.insertText(tag);

                return;
            }

            const $body = $(bodyView.element.querySelector('textarea.main-element'));

            let text = $body.val();
            text += tag;
            $body.val(text);
        });
    }
}

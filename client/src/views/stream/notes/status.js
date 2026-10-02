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

import NoteStreamView from 'views/stream/note';

/**
 * Legacy as of v9.2.0.
 */
class StatusNoteStreamView extends NoteStreamView {

    template = 'stream/notes/status'
    messageName = 'status'

    data() {
        return {
            ...super.data(),
            style: this.style,
            statusText: this.statusText,
            iconHtml: this.getIconHtml(),
        };
    }

    init() {
        if (this.getUser().isAdmin()) {
            this.isRemovable = true;
        }

        super.init();
    }

    setup() {
        const data = this.model.get('data');

        const parentType = this.model.attributes.parentType;

        const field = this.getMetadata().get(`scopes.${parentType}.statusField`) ?? '';
        const value = data.value;

        this.style = data.style || 'default';
        this.statusText = this.getLanguage().translateOption(value, field, parentType);

        this.statusStyle = this.getMetadata()
            .get(`entityDefs.${parentType}.fields.${field}.style.${value}`) ||
            'default';

        let fieldLabel = this.translate(field, 'fields', parentType);

        if (!this.isToUpperCaseStringItems()) {
            fieldLabel = fieldLabel.toLowerCase();
        }

        this.messageData['field'] = fieldLabel;

        this.createMessage();
    }
}

export default StatusNoteStreamView;

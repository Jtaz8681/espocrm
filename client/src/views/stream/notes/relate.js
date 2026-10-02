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

class RelateNoteStreamView extends NoteStreamView {

    template = 'stream/notes/create-related'
    messageName = 'relate'

    data() {
        return {
            ...super.data(),
            relatedTypeString: this.translateEntityType(this.entityType),
            iconHtml: this.getIconHtml(this.entityType, this.entityId),
        };
    }

    init() {
        if (this.getUser().isAdmin()) {
            this.isRemovable = true;
        }

        super.init();
    }

    setup() {
        const data = this.model.get('data') || {};

        this.entityType = this.model.get('relatedType') || data.entityType || null;
        this.entityId = this.model.get('relatedId') || data.entityId || null;
        this.entityName = this.model.get('relatedName') ||  data.entityName || null;

        this.messageData['relatedEntityType'] = this.translateEntityType(this.entityType);

        if (this.model.attributes.relatedId) {
            this.messageData['relatedEntity'] = 'field:related';
        } else {
            // bc
            this.messageData['relatedEntity'] =
                $('<a>')
                    .attr('href', `#${this.entityType}/view/${this.entityId}`)
                    .text(this.entityName)
                    .attr('data-scope', this.entityType)
                    .attr('data-id', this.entityId);
        }

        this.createMessage();
    }
}

export default RelateNoteStreamView;

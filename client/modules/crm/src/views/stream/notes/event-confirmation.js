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

class EventConfirmationNoteView extends NoteStreamView {

    // language=Handlebars
    templateContent = `
        {{#unless noEdit}}
        <div class="pull-right right-container cell-buttons">
        {{{right}}}
        </div>
        {{/unless}}

        <div class="stream-head-container">
            <div class="pull-left">
                {{{avatar}}}
            </div>
            <div class="stream-head-text-container">
                {{#if iconHtml}}{{{iconHtml}}}{{/if}}
                <span class="text-muted message">{{{message}}}</span>
            </div>
        </div>
        <div class="stream-post-container">
            <span class="{{statusIconClass}} text-{{style}}"></span>
        </div>
        <div class="stream-date-container">
            <a class="text-muted small" href="#Note/view/{{model.id}}">{{{createdAt}}}</a>
        </div>
    `

    data() {
        const statusIconClass = ({
            'success': 'fas fa-check fa-sm',
            'danger': 'fas fa-times fa-sm',
            'warning': 'fas fa-question fa-sm',
        })[this.style] || '';

        return {
            ...super.data(),
            statusText: this.statusText,
            style: this.style,
            statusIconClass: statusIconClass,
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
        this.inviteeType = this.model.get('relatedType');
        this.inviteeId = this.model.get('relatedId');
        this.inviteeName = this.model.get('relatedName');

        const data = this.model.get('data') || {};

        const status = data.status || 'Tentative';
        this.style = data.style || 'default';
        this.statusText = this.getLanguage().translateOption(status, 'acceptanceStatus', 'Meeting');

        this.messageName = 'eventConfirmation' + status;

        if (this.isThis) {
            this.messageName += 'This';
        }

        this.messageData['invitee'] =
            $('<a>')
                .attr('href', '#' + this.inviteeType + '/view/' + this.inviteeId)
                .attr('data-id', this.inviteeId)
                .attr('data-scope', this.inviteeType)
                .text(this.inviteeName);

        this.createMessage();
    }
}

// noinspection JSUnusedGlobalSymbols
export default EventConfirmationNoteView;

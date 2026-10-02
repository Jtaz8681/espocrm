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

import BaseNotificationItemView from 'views/notification/items/base';

// noinspection JSUnusedGlobalSymbols
export default class CollaboratingNotificationItemView extends BaseNotificationItemView {

    // language=Handlebars
    templateContent = `
        <div class="stream-head-container">
            <div class="pull-left">
                {{{avatar}}}
            </div>
            <div class="stream-head-text-container text-muted">
                {{#if iconHtml}}{{{iconHtml}}}{{/if}}<span class="message"">{{{message}}}</span>
            </div>
        </div>

        <div class="stream-date-container">
            <span class="text-muted small">{{{createdAt}}}</span>
        </div>
    `

    messageName = 'addedToCollaborators'

    data() {
        const iconHtml = this.model.attributes.relatedType && this.model.attributes.relatedId ?
            this.getIconHtml(this.model.attributes.relatedType, this.model.attributes.relatedId) : null;

        return {
            ...super.data(),
            iconHtml: iconHtml,
        };
    }

    setup() {
        this.userId = this.model.attributes.createdById;

        this.messageData['user'] = (() => {
            const element = document.createElement('a');
            element.href = `#User/view/${this.model.attributes.createdById}`;
            element.dataset.id = this.model.attributes.createdById;
            element.dataset.scope = 'User';
            element.textContent = this.model.attributes.createdByName;

            return element;
        })();

        this.messageData['entityType'] = this.translateEntityType(this.model.attributes.relatedType);

        this.messageData['entity'] = 'field:related';

        this.createMessage();
    }
}

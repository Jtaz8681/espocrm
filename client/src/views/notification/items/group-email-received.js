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
export default class GroupNoteNotificationItemView extends BaseNotificationItemView {

    // language=Handlebars
    templateContent = `
        <div class="stream-head-container">
            <div class="pull-left stream-head-left-icon-container">
                <span
                    class="far fa-envelope text-soft icon"
                >
            </div>
            <div class="stream-head-text-container">
                <span class="text-muted message">{{{message}}}</span>
            </div>
        </div>

        <div class="stream-date-container">
            <span class="text-muted small">{{{createdAt}}}</span>
        </div>
    `

    data() {
        return {
            ...super.data(),
        };
    }

    setup() {
        const newCount = this.model.attributes.groupedUnreadCount ?? 0;

        this.messageData['number'] = newCount.toString();

        this.messageName = 'groupEmailsReceivedNew';

        if (newCount === 0) {
            this.messageName = 'groupEmailsReceived';
        }

        this.createMessage();
    }
}

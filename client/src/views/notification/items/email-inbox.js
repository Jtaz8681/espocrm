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

class EmailInboxNotificationItemView extends BaseNotificationItemView {

    messageName = 'emailInbox'

    // language=Handlebars
    templateContent = `
        <div class="stream-head-container">
            <div class="pull-left">{{{avatar}}}</div>
            <div class="stream-head-text-container">
                <span
                    class="fas fa-envelope text-muted action icon"
                    style="cursor: pointer;"
                    title="{{translate 'View'}}"
                    data-action="quickView"
                    data-id="{{model.attributes.relatedId}}"
                    data-scope="Email"
                ></span><span class="text-muted message">{{{message}}}</span>
            </div>
        </div>
        <div class="stream-date-container">
            <span class="text-muted small">{{{createdAt}}}</span>
        </div>
    `

    setup() {
        /** @type {{userId: string, userName: string, emailName: string}} */
        const data = this.model.attributes.data || {};

        this.userId = data.userId;

        this.messageData['entityType'] = this.translateEntityType('Email');

        const entity = document.createElement('a');
        entity.href = `#Email/view/${this.model.attributes.relatedId}`;
        entity.dataset.id = this.model.attributes.relatedId;
        entity.dataset.scope = 'Email';
        entity.innerText = data.emailName;

        const user = document.createElement('a');
        user.href = `#User/view/${data.userId}`;
        user.dataset.id = data.userId;
        user.dataset.scope = 'User';
        user.innerText = data.userName;

        this.messageData['entity'] = entity;
        this.messageData['user'] = user;

        this.createMessage();
    }
}

export default EmailInboxNotificationItemView;

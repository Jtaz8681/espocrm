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

define('crm:views/notification/items/event-attendee', ['views/notification/items/base'], function (Dep) {

    return Dep.extend({

        messageName: 'eventAttendee',

        templateContent: `
            <div class="stream-head-container">
                <div class="pull-left">{{{avatar}}}</div>
                <div class="stream-head-text-container">
                    <span class="text-muted message">{{{message}}}</span>
                </div>
            </div>
            <div class="stream-date-container">
                <span class="text-muted small">{{{createdAt}}}</span>
            </div>
        `,

        setup: function () {
            let data = this.model.get('data') || {};

            this.userId = data.userId;

            this.messageData['entityType'] = this.translateEntityType(data.entityType);

            this.messageData['entity'] =
                $('<a>')
                    .attr('href', '#' + data.entityType + '/view/' + data.entityId)
                    .attr('data-id', data.entityId)
                    .attr('data-scope', data.entityType)
                    .text(data.entityName);

            this.messageData['user'] =
                $('<a>')
                    .attr('href', '#User/view/' + data.userId)
                    .attr('data-id', data.userId)
                    .attr('data-scope', 'User')
                    .text(data.userName);

            this.createMessage();
        },
    });
});

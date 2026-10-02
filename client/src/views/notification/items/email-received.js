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

class EmailReceivedNotificationItemView extends BaseNotificationItemView {

    messageName = 'emailReceived'

    template = 'notification/items/email-received'

    data() {
        return {
            ...super.data(),
            emailId: this.emailId,
            emailName: this.emailName,
        };
    }

    setup() {
        const data = /** @type {Record} */this.model.get('data') || {};

        this.userId = data.userId;

        this.messageData['entityType'] = this.translateEntityType(data.entityType);

        if (data.personEntityId) {
            this.messageData['from'] =
                $('<a>')
                    .attr('href', '#' + data.personEntityType + '/view/' + data.personEntityId)
                    .attr('data-id', data.personEntityId)
                    .attr('data-scope', data.personEntityType)
                    .text(data.personEntityName);
        }
        else {
            const text = data.fromString || this.translate('empty address');

            this.messageData['from'] = $('<span>').text(text);
        }

        this.emailId = data.emailId;
        this.emailName = data.emailName;

        this.createMessage();
    }
}

export default EmailReceivedNotificationItemView;

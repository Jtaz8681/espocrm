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

class AssignNotificationItemView extends BaseNotificationItemView {

    messageName = 'assign'

    template = 'notification/items/assign'

    setup() {
        const data = this.model.get('data') || {};

        this.userId = data.userId;

        this.messageData['entityType'] = this.translateEntityType(data.entityType);

        this.messageData['entity'] = 'field:related';

        this.messageData['user'] =
            $('<a>')
                .attr('href', '#User/view/' + data.userId)
                .attr('data-id', data.userId)
                .attr('data-scope', 'User')
                .text(data.userName);

        this.createMessage();
    }
}

export default AssignNotificationItemView;

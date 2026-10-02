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

class SystemNotificationItemView extends BaseNotificationItemView {

    template = 'notification/items/system'

    data() {
        return {
            ...super.data(),
            message: this.model.get('message'),
        };
    }

    setup() {
        const data = this.model.get('data') || {};

        this.userId = data.userId;
    }
}

export default SystemNotificationItemView;

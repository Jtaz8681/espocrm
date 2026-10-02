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

import TestSendView from 'views/email-account/fields/test-send';

export default class extends TestSendView {

    getSmtpData() {
        return {
            server: this.model.get('smtpHost'),
            port: this.model.get('smtpPort'),
            auth: this.model.get('smtpAuth'),
            security: this.model.get('smtpSecurity'),
            username: this.model.get('smtpUsername'),
            password: this.model.get('smtpPassword') || null,
            authMechanism: this.model.get('smtpAuthMechanism'),
            fromName: this.model.get('fromName'),
            fromAddress: this.model.get('emailAddress'),
            type: 'inboundEmail',
            id: this.model.id,
        };
    }
}

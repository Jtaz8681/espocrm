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

import IntegrationsEditView from 'views/admin/integrations/edit';

/**
 * @deprecated As of v9.2.
 * @todo Remove in v9.3.
 */
export default class IntegrationsOauth2EditView extends IntegrationsEditView {

    template = 'admin/integrations/oauth2'

    data() {
        const redirectUri = this.redirectUri || (this.getConfig().get('siteUrl') + '?entryPoint=oauthCallback');

        return {
            ...super.data(),
            redirectUri: redirectUri,
        };
    }
}

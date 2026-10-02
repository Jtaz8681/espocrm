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

import VarcharFieldView from 'views/fields/varchar';

export default class extends VarcharFieldView {

    detailTemplate = 'attachment/fields/name/detail'

    data() {
        const data = super.data();

        let url = this.getBasePath() + '?entryPoint=download&id=' + this.model.id;

        if (this.getUser().get('portalId')) {
            url += '&portalId=' + this.getUser().get('portalId');
        }

        data.url = url;

        return data;
    }
}

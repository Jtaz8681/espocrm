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

    setup() {
        super.setup();

        this.listenTo(this, 'change', () => {
            let value = this.model.get('customId');

            if (!value || value === '') {
                return;
            }

            value = value.replace(/ /i, '-').toLowerCase();
            value = encodeURIComponent(value);

            this.model.set('customId', value);
        });
    }
}

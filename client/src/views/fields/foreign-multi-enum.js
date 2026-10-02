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

import MultiEnumFieldView from 'views/fields/multi-enum';
import ForeignArrayFieldView from 'views/fields/foreign-array';
import Helper from 'helpers/misc/foreign-field';

class ForeignMultiEnumFieldView extends MultiEnumFieldView {

    type = 'foreign'

    /**
     * @private
     * @type {string}
     */
    foreignEntityType

    setup() {
        const helper = new Helper(this);
        const foreignParams = helper.getForeignParams();

        for (const param in foreignParams) {
            this.params[param] = foreignParams[param];
        }

        this.foreignEntityType = helper.getEntityType();

        super.setup();
    }

    setupOptions() {
        ForeignArrayFieldView.prototype.setupOptions.call(this);
    }
}

export default ForeignMultiEnumFieldView;

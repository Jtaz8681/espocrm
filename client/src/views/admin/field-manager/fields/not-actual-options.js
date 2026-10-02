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

class NotActualOptionsFieldView extends MultiEnumFieldView {

    setup() {
        super.setup();

        this.params.options = Espo.Utils.clone(this.model.get('options')) || [];

        this.listenTo(this.model, 'change:options', (/** import('model').default */model) => {
            this.params.options = Espo.Utils.clone(model.get('options')) || [];

            this.reRender();
        });
    }
}

export default NotActualOptionsFieldView;

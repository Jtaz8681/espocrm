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

/** @module views/record/edit-side */

import DetailSideRecordView from 'views/record/detail-side';

class EditSideRecordView extends DetailSideRecordView {

    /** @inheritDoc */
    mode = 'edit'

    /** @inheritDoc */
    defaultPanelDefs = {
        name: 'default',
        label: false,
        view: 'views/record/panels/side',
        isForm: true,
        options: {
            fieldList: [
                {
                    name: ':assignedUser'
                },
                {
                    name: 'teams',
                    view: 'views/fields/teams'
                }
            ]
        }
    }
}

export default EditSideRecordView;

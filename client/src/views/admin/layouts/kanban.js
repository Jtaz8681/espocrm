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

import LayoutListView from 'views/admin/layouts/list';

class LayoutKanbanView extends LayoutListView {

    dataAttributeList = [
        'name',
        'link',
        'align',
        'view',
        'isLarge',
        'isMuted',
        'hidden',
    ]

    dataAttributesDefs = {
        link: {type: 'bool'},
        isLarge: {type: 'bool'},
        isMuted: {type: 'bool'},
        width: {type: 'float'},
        align: {
            type: 'enum',
            options: ['left', 'right'],
        },
        view: {
            type: 'varchar',
            readOnly: true,
        },
        name: {
            type: 'varchar',
            readOnly: true,
        },
        hidden: {
            type: 'bool',
        },
    }

    editable = true
    ignoreList = []
    ignoreTypeList = []
}

export default LayoutKanbanView;

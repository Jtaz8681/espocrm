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

import LinkMultipleWithRoleFieldView from 'views/fields/link-multiple-with-role';

// noinspection JSUnusedGlobalSymbols
export default class extends LinkMultipleWithRoleFieldView {

    forceRoles = true
    roleType = 'varchar'
    columnName = 'group'
    roleMaxLength = 255

    setup() {
        super.setup();

        this.rolePlaceholderText = this.translate('IdP Group', 'labels', 'Settings');
    }
}

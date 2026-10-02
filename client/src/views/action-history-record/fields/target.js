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

import LinkParentFieldView from 'views/fields/link-parent';

class ActionHistoryRecordTargetFieldView extends LinkParentFieldView
{
    displayScopeColorInListMode = true

    ignoreScopeList = [
        'Preferences',
        'ExternalAccount',
        'Notification',
        'Note',
        'ArrayValue',
    ]

    setup() {
        super.setup();

        this.foreignScopeList = this.getMetadata().getScopeEntityList().filter(item => {
            if (!this.getUser().isAdmin() && !this.getAcl().checkScopeHasAcl(item)) {
                return false;
            }

            if (this.ignoreScopeList.includes(item)) {
                return false;
            }

            if (!this.getAcl().checkScope(item)) {
                return false;
            }

            return true;
        });

        this.getLanguage().sortEntityList(this.foreignScopeList);

        this.foreignScope = this.model.get(this.typeName) || this.foreignScopeList[0];
    }
}

export default ActionHistoryRecordTargetFieldView;

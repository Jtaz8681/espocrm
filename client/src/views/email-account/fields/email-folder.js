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

import LinkFieldView from 'views/fields/link';

export default class extends LinkFieldView {

    createDisabled = true
    autocompleteDisabled = true

    getSelectFilters() {
        if (this.getUser().isAdmin() && this.model.get('assignedUserId')) {
            return {
                assignedUser: {
                    type: 'equals',
                    attribute: 'assignedUserId',
                    value: this.model.get('assignedUserId'),
                    data: {
                        type: 'is',
                        nameValue: this.model.get('assignedUserName'),
                    },
                }
            };
        }
    }

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:assignedUserId', (model, e, o) => {
            if (!o.ui) {
                return;
            }

            this.model.set({
                emailFolderId: null,
                emailFolderName: null,
            });
        });
    }
}

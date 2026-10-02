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

import DefaultRowActionsView from 'views/record/row-actions/default';

export default class extends DefaultRowActionsView {

    getActionList() {
        if (!this.options.acl.edit) {
            return [];
        }

        if (this.model.get('isInstalled')) {
            return [
                {
                    action: 'uninstall',
                    label: 'Uninstall',
                    data: {
                        id: this.model.id,
                    },
                },
            ];
        }

        return [
            {
                action: 'install',
                label: 'Install',
                data: {
                    id: this.model.id,
                },
            },
            {
                action: 'quickRemove',
                label: 'Remove',
                data: {
                    id: this.model.id,
                },
            },
        ];
    }
}

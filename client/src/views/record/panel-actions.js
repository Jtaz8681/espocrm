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

import View from 'view';

class PanelActionsView extends View {

    template = 'record/panel-actions'

    data() {
        return {
            defs: this.options.defs,
            buttonList: this.getButtonList(),
            actionList: this.getActionList(),
            entityType: this.options.entityType,
            scope: this.options.scope,
        };
    }

    setup() {
        this.buttonList = this.options.defs.buttonList || [];
        this.actionList = this.options.defs.actionList || [];
        this.defs = this.options.defs;
    }

    getButtonList() {
        const list = [];

        this.buttonList.forEach(item => {
            if (item.hidden) {
                return;
            }

            list.push(item);
        });

        return list;
    }

    getActionList() {
        return this.actionList
            .filter(item => !item.hidden)
            .map(item => {
                item = Espo.Utils.clone(item);

                if (item.action) {
                    item.data = Espo.Utils.clone(item.data || {});
                    item.data.panel = this.options.defs.name;
                }

                return item;
            });
    }
}

export default PanelActionsView;

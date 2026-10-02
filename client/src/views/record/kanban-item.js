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

class KanbanRecordItem extends View {

    template = 'record/kanban-item'

    data() {
        return {
            layoutDataList: this.layoutDataList,
            rowActionsDisabled: this.rowActionsDisabled,
            isStarred: this.hasStars && this.model.attributes.isStarred,
        };
    }

    setup() {
        this.itemLayout = this.options.itemLayout;
        this.rowActionsView = this.options.rowActionsView;
        this.rowActionsDisabled = this.options.rowActionsDisabled;
        this.hasStars = this.options.hasStars;

        this.layoutDataList = [];

        this.itemLayout.forEach((item, i) => {
            const name = item.name;
            const key = name + 'Field';

            const o = {
                name: name,
                isAlignRight: item.align === 'right',
                isLarge: item.isLarge,
                isMuted: item.isMuted,
                isFirst: i === 0,
                key: key,
            };

            this.layoutDataList.push(o);

            let viewName = item.view || this.model.getFieldParam(name, 'view');

            if (!viewName) {
                const type = this.model.getFieldType(name) || 'base';

                viewName = this.getFieldManager().getViewName(type);
            }

            let mode = 'list';

            if (item.link) {
                mode = 'listLink';
            }

            this.createView(key, viewName, {
                model: this.model,
                name: name,
                mode: mode,
                readOnly: true,
                selector: '.field[data-name="'+name+'"]',
            });
        });

        if (!this.rowActionsDisabled) {
            const acl = {
                edit: this.getAcl().checkModel(this.model, 'edit'),
                delete: this.getAcl().checkModel(this.model, 'delete'),
            };

            this.createView('itemMenu', this.rowActionsView, {
                selector: '.item-menu-container',
                model: this.model,
                acl: acl,
                moveOverRowAction: this.options.moveOverRowAction,
                statusFieldIsEditable: this.options.statusFieldIsEditable,
                rowActionHandlers: this.options.rowActionHandlers || {},
                additionalActionList: this.options.additionalRowActionList,
                scope: this.options.scope,
            });
        }
    }
}

export default KanbanRecordItem;

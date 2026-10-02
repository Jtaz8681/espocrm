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

import RowActionHandler from 'handlers/row-action';

export default class PipelineMoveRowActionHandler extends RowActionHandler {

    isAvailable(model, action) {
        const collection = model.collection;

        if (!collection) {
            return false;
        }

        if (action === 'moveToTop' || action === 'moveUp') {
            if (collection.indexOf(model) <= 0) {
                return false;
            }
        }

        if (action === 'moveToBottom' || action === 'moveDown') {
            if (collection.indexOf(model) >= collection.total - 1) {
                return false;
            }
        }

        return collection.orderBy === 'order' && collection.order === 'asc';
    }

    async process(model, action) {
        let type;

        if (action === 'moveToTop') {
            type = 'top';
        } else if (action === 'moveToBottom') {
            type = 'bottom';
        } else  if (action === 'moveUp') {
            type = 'up';
        } else if (action === 'moveDown') {
            type = 'down';
        } else {
            throw new Error();
        }

        Espo.Ui.notifyWait();

        await Espo.Ajax.postRequest(`${model.entityType}/${model.id}/move/${type}`, {
            whereGroup: this.collection.getWhere(),
        });

        await this.collection.fetch();

        Espo.Ui.notify();
    }
}

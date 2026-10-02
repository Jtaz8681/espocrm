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

import ListRecordView from 'views/record/list';

export default class extends ListRecordView {

    rowActionsView = 'views/admin/auth-token/record/row-actions/default'
    massActionList = ['remove', 'setInactive']
    checkAllResultMassActionList = ['remove', 'setInactive']

    // noinspection JSUnusedGlobalSymbols
    massActionSetInactive() {
        let ids = null;
        const allResultIsChecked = this.allResultIsChecked;

        if (!allResultIsChecked) {
            ids = this.checkedList;
        }

        const attributes = {
            isActive: false,
        };

        Espo.Ajax
            .postRequest('MassAction', {
                action: 'update',
                entityType: this.entityType,
                params: {
                    ids: ids || null,
                    where: (!ids || ids.length === 0) ? this.getWhereForAllResult() : null,
                    searchParams: (!ids || ids.length === 0) ? this.collection.data : null,
                },
                data: attributes,
            })
            .then(() => {
                this.collection
                    .fetch()
                    .then(() => {
                        Espo.Ui.success(this.translate('Done'));

                        if (ids) {
                            ids.forEach(id => {
                                this.checkRecord(id);
                            });
                        }
                    });
            });
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {Record} data
     */
    actionSetInactive(data) {
        if (!data.id) {
            return;
        }

        const model = this.collection.get(data.id);

        if (!model) {
            return;
        }

        Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

        model
            .save({'isActive': false}, {patch: true})
            .then(() => {
                Espo.Ui.notify(false);
            });
    }
}

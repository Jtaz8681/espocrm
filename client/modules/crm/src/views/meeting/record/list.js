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

    rowActionsView = 'modules/crm/views/meeting/record/row-actions/default'

    setup() {
        super.setup();

        if (
            this.getAcl().checkScope(this.entityType, 'edit') &&
            this.getAcl().checkField(this.entityType, 'status', 'edit')
        ) {
            this.massActionList.push('setHeld');
            this.massActionList.push('setNotHeld');
        }
    }

    /**
     * @protected
     * @param {Record} data
     */
    async actionSetHeld(data) {
        const id = data.id;

        if (!id) {
            return;
        }

        const model = this.collection.get(id);

        if (!model) {
            return;
        }

        Espo.Ui.notify(this.translate('saving', 'messages'));

        await model.save({status: 'Held'}, {patch: true});

        Espo.Ui.success(this.translate('Saved'));
    }

    /**
     * @protected
     * @param {Record} data
     */
    async actionSetNotHeld(data) {
        const id = data.id;

        if (!id) {
            return;
        }

        const model = this.collection.get(id);

        if (!model) {
            return;
        }

        Espo.Ui.notify(this.translate('saving', 'messages'));

        await model.save({status: 'Not Held'}, {patch: true});

        Espo.Ui.success(this.translate('Saved'));
    }

    // noinspection JSUnusedGlobalSymbols
    async massActionSetHeld() {
        const data = {};

        data.ids = this.checkedList;

        Espo.Ui.notify(this.translate('saving', 'messages'));

        await Espo.Ajax.postRequest(`${this.collection.entityType}/action/massSetHeld`, data);

        Espo.Ui.success(this.translate('Saved'));

        await this.collection.fetch();

        data.ids.forEach(id => {
            if (this.collection.get(id)) {
                this.checkRecord(id);
            }
        });
    }

    // noinspection JSUnusedGlobalSymbols
    async massActionSetNotHeld() {
        const data = {};

        data.ids = this.checkedList;

        Espo.Ui.notify(this.translate('saving', 'messages'));

        await Espo.Ajax.postRequest(`${this.collection.entityType}/action/massSetNotHeld`, data);

        Espo.Ui.success(this.translate('Saved'));

        await this.collection.fetch();

        data.ids.forEach(id => {
            if (this.collection.get(id)) {
                this.checkRecord(id);
            }
        });
    }
}

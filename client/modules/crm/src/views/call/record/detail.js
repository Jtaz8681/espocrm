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

import DetailRecordView from 'views/record/detail';

export default class extends DetailRecordView {

    duplicateAction = true

    setupActionItems() {
        super.setupActionItems();

        if (!this.getAcl().checkModel(this.model, 'edit')) {
            return;
        }

        const historyStatusList = this.getMetadata().get(`scopes.${this.entityType}.historyStatusList`) || [];

        if (
            !historyStatusList.includes('Held') ||
            !historyStatusList.includes('Not Held')
        ) {
            return;
        }

        this.dropdownItemList.push({
            'label': 'Set Held',
            'name': 'setHeld',
            onClick: () => this.actionSetHeld(),
            iconClass: 'fas fa-check',
        });

        this.dropdownItemList.push({
            'label': 'Set Not Held',
            'name': 'setNotHeld',
            onClick: () => this.actionSetNotHeld(),
        });

        const control = () => {
            if (
                historyStatusList.includes(this.model.attributes.status) ||
                !this.getAcl().checkModel(this.model, 'edit')
            ) {
                this.hideActionItem('setHeld');
                this.hideActionItem('setNotHeld');
            } else {
                this.showActionItem('setHeld');
                this.showActionItem('setNotHeld');
            }
        };

        control();

        this.model.onSync({
            owner: this,
            callback: () => control(),
        });
    }

    manageAccessEdit(second) {
        super.manageAccessEdit(second);

        if (second) {
            if (!this.getAcl().checkModel(this.model, 'edit', true)) {
                this.hideActionItem('setHeld');
                this.hideActionItem('setNotHeld');
            }
        }
    }

    actionSetHeld() {
        this.model.save({status: 'Held'}, {patch: true})
            .then(() => {
                Espo.Ui.success(this.translate('Saved'));
            });
    }

    actionSetNotHeld() {
        this.model.save({status: 'Not Held'}, {patch: true})
            .then(() => {
                Espo.Ui.success(this.translate('Saved'));
            });
    }
}

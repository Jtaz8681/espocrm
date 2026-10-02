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

    setup() {
        super.setup();

        /** @type string[] */
        const options = this.model.getFieldParam('status', 'options') ?? [];

        if (options.includes('Held')) {
            this.dropdownItemList.push({
                labelTranslation: `${this.scope}.labels.Set Held`,
                name: 'setHeld',
                onClick: () => this.actionSetHeld(),
            });
        }

        if (options.includes('Not Held')) {
            this.dropdownItemList.push({
                labelTranslation: `${this.scope}.labels.Set Not Held`,
                name: 'setNotHeld',
                onClick: () => this.actionSetNotHeld(),
            });
        }

        this.controlHeldButtons();

        this.model.onSync({
            owner: this,
            callback: () => this.controlHeldButtons(),
        });
    }

    actionSetHeld() {
        this.model
            .save({status: 'Held'}, {patch: true})
            .then(() => {
                Espo.Ui.success(this.translate('Saved', 'labels', 'Meeting'));

                this.controlHeldButtons();
            });
    }

    actionSetNotHeld() {
        this.model
            .save({status: 'Not Held'}, {patch: true})
            .then(() => {
                Espo.Ui.success(this.translate('Saved', 'labels', 'Meeting'));
            });
    }

    /**
     * @private
     */
    controlHeldButtons() {
        if (
            this.getAcl().checkModel(this.model, 'edit') &&
            ['Held', 'Not Held'].includes(this.model.attributes.status)
        ) {
            this.hideActionItem('setHeld');
            this.hideActionItem('setNotHeld');
        } else {
            this.showActionItem('setHeld');
            this.showActionItem('setNotHeld');
        }
    }
}

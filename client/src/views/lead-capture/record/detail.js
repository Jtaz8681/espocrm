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

    setupActionItems() {
        super.setupActionItems();

        this.addDropdownItem({
            label: 'Generate New API Key',
            name: 'generateNewApiKey',
            onClick: () => this.actionGenerateNewApiKey(),
        });

        this.addDropdownItem({
            label: 'Generate New Form ID',
            name: 'generateNewFormId',
            onClick: () => this.actionGenerateNewFormId(),
        });
    }

    actionGenerateNewApiKey() {
        this.confirm(this.translate('confirmation', 'messages'), () => {
            Espo.Ajax.postRequest('LeadCapture/action/generateNewApiKey', {id: this.model.id})
                .then(data => {
                    this.model.set(data);

                    Espo.Ui.success(this.translate('Done'));
                });
        });
    }

    async actionGenerateNewFormId() {
        await this.confirm(this.translate('confirmation', 'messages'));

        const data = await Espo.Ajax.postRequest('LeadCapture/action/generateNewFormId', {id: this.model.id});

        this.model.set(data);

        Espo.Ui.success(this.translate('Done'));
    }
}

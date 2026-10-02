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

import RelationshipPanelView from 'views/record/panels/relationship';
import RecordModal from 'helpers/record-modal';

// noinspection JSUnusedGlobalSymbols
export default class CampaignLogRecordsPanelView extends RelationshipPanelView {

    filterList = [
        "all",
        "sent",
        "opened",
        "optedOut",
        "bounced",
        "clicked",
        "optedIn",
        "leadCreated",
    ]

    setup() {
        if (this.getAcl().checkScope('TargetList', 'create')) {
            this.actionList.push({
                action: 'createTargetList',
                label: 'Create Target List',
            });
        }

        this.filterList = Espo.Utils.clone(this.filterList);

        if (!this.getConfig().get('massEmailOpenTracking')) {
            const i = this.filterList.indexOf('opened');

            if (i >= 0) {
                this.filterList.splice(i, 1);
            }
        }

        super.setup();
    }

    actionCreateTargetList() {
        const attributes = {
            sourceCampaignId: this.model.id,
            sourceCampaignName: this.model.attributes.name,
        };

        if (!this.collection.data.primaryFilter) {
            attributes.includingActionList = [];
        } else {
            const status = Espo.Utils.upperCaseFirst(this.collection.data.primaryFilter)
                .replace(/([A-Z])/g, ' $1');

            attributes.includingActionList = [status];
        }

        const helper = new RecordModal();

        helper.showCreate(this, {
            entityType: 'TargetList',
            attributes: attributes,
            fullFormDisabled: true,
            layoutName: 'createFromCampaignLog',
            afterSave: () => {
                Espo.Ui.success(this.translate('Done'));
            },
            beforeRender: view => {
                view.getRecordView().setFieldRequired('includingActionList')
            },
        });
    }
}

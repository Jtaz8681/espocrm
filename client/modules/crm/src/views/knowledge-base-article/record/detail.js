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

import KnowledgeBaseHelper from 'modules/crm/knowledge-base-helper';
import DetailRecordView from 'views/record/detail';

class KnowledgeBaseRecordDetailView extends DetailRecordView {

    saveAndContinueEditingAction = true

    setup() {
        super.setup();

        if (this.getUser().isPortal()) {
            this.sideDisabled = true;
        }

        if (this.getAcl().checkScope('Email', 'create')) {
            this.dropdownItemList.push({
                'label': 'Send in Email',
                'name': 'sendInEmail',
                iconClass: 'far fa-paper-plane',
            });
        }

        if (
            this.getUser().isPortal() &&
            !this.getAcl().checkScope(this.scope, 'edit') &&
            !this.model.getLinkMultipleIdList('attachments').length
        ) {
            this.hideField('attachments');

            this.listenToOnce(this.model, 'sync', () => {
                if (this.model.getLinkMultipleIdList('attachments').length) {
                    this.showField('attachments');
                }
            });
        }
    }

    // noinspection JSUnusedGlobalSymbols
    actionSendInEmail() {
        Espo.Ui.notifyWait();

        const helper = new KnowledgeBaseHelper(this.getLanguage());

        helper.getAttributesForEmail(this.model, {}, attributes => {
            const viewName = this.getMetadata().get('clientDefs.Email.modalViews.compose') ||
                'views/modals/compose-email';

            this.createView('composeEmail', viewName, {
                attributes: attributes,
                selectTemplateDisabled: true,
                signatureDisabled: true,
            }, view => {
                Espo.Ui.notify(false);

                view.render();
            });
        });
    }
}

export default KnowledgeBaseRecordDetailView;

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

import RecordListDashletView from 'views/dashlets/abstract/record-list';

class EmailsDashletView extends RecordListDashletView {

    name = 'Emails'
    scope ='Emails'

    rowActionsView = 'views/email/record/row-actions/dashlet'
    listView = 'views/email/record/list-expanded'

    setupActionList() {
        if (this.getAcl().checkScope(this.scope, 'create')) {
            this.actionList.unshift({
                name: 'compose',
                text: this.translate('Compose Email', 'labels', this.scope),
                iconClass: 'fas fa-plus',
            });
        }
    }

    // noinspection JSUnusedGlobalSymbols
    actionCompose() {
        const attributes = this.getCreateAttributes() || {};

        Espo.Ui.notifyWait();

        const viewName = this.getMetadata().get('clientDefs.' + this.scope + '.modalViews.compose') ||
            'views/modals/compose-email';

        this.createView('modal', viewName, {
            scope: this.scope,
            attributes: attributes,
        }, view => {
            view.render();

            Espo.Ui.notify(false);

            this.listenToOnce(view, 'after:save', () => {
                this.actionRefresh();
            });
        });
    }

    /**
     * @return {module:search-manager~data}
     */
    getSearchData() {
        return {
            'advanced': [
                {
                    'attribute': 'folderId',
                    'type': 'inFolder',
                    'value': this.getOption('folder') || 'inbox',
                }
            ]
        };
    }
}

export default EmailsDashletView;

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

import SidePanelView from 'views/record/panels/side';

// noinspection JSUnusedGlobalSymbols
export default class extends SidePanelView {

    controlStatsFields() {
        const type = this.model.attributes.type;

        let fieldList;

        switch (type) {
            case 'Email':
            case 'Newsletter':
                fieldList = [
                    'sentCount',
                    'openedCount',
                    'clickedCount',
                    'optedOutCount',
                    'bouncedCount',
                    'leadCreatedCount',
                    'optedInCount',
                    'revenue',
                ];

                break;

            case 'Informational Email':
                fieldList = [
                    'sentCount',
                    'bouncedCount',
                ];

                break;

            case 'Web':
                fieldList = ['leadCreatedCount', 'optedInCount', 'revenue'];

                break;

            case 'Television':
            case 'Radio':
                fieldList = ['leadCreatedCount', 'revenue'];

                break;

            case 'Mail':
                fieldList = ['sentCount', 'leadCreatedCount', 'optedInCount', 'revenue'];

                break;

            default:
                fieldList = ['leadCreatedCount', 'revenue'];
        }

        if (!this.getConfig().get('massEmailOpenTracking')) {
            const i = fieldList.indexOf('openedCount');

            if (i > -1) {
                fieldList.splice(i, 1);
            }
        }

        this.statsFieldList.forEach(item => {
            this.options.recordViewObject.hideField(item);
        });

        fieldList.forEach(item => {
            this.options.recordViewObject.showField(item);
        });

        if (!this.getAcl().checkScope('Lead')) {
            this.options.recordViewObject.hideField('leadCreatedCount', true);
        }

        if (!this.getAcl().checkScope('Opportunity')) {
            this.options.recordViewObject.hideField('revenue', true);
        }
    }

    setupFields() {
        this.fieldList = [
            'sentCount',
            'openedCount',
            'clickedCount',
            'optedOutCount',
            'bouncedCount',
            'leadCreatedCount',
            'optedInCount',
            'revenue',
        ];

        this.statsFieldList = this.fieldList;
    }

    setup() {
        super.setup();

        this.controlStatsFields();

        this.listenTo(this.model, 'change:type', () => this.controlStatsFields());
    }
}

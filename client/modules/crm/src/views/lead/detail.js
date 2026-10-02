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

import DetailView from 'views/detail';


class LeadDetailView extends DetailView {

    setup() {
        super.setup();

        this.addMenuItem('buttons', {
            name: 'convert',
            action: 'convert',
            label: 'Convert',
            acl: 'edit',
            hidden: !this.isConvertable(),
            onClick: () => this.actionConvert(),
        });

        this.listenTo(this.model, 'sync', () => {
            this.isConvertable() ?
                this.showHeaderActionItem('convert') :
                this.hideHeaderActionItem('convert');
        });
    }

    isConvertable() {
        const notActualList = [
            ...(this.getMetadata().get(`entityDefs.Lead.fields.status.notActualOptions`) || []),
            'Converted',
       ];

        return !notActualList.includes(this.model.get('status')) && this.model.has('status');
    }

    actionConvert() {
        this.getRouter().navigate(`${this.model.entityType}/convert/id=${this.model.id}`, {trigger: true});
    }
}

export default LeadDetailView;

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

import ListView from 'views/list';

export default class extends ListView {

    searchPanel = false

    setup() {
        super.setup();

        this.addMenuItem('buttons', {
            link: '#Admin/jobs',
            text: this.translate('Jobs', 'labels', 'Admin'),
        });

        this.createView('search', 'views/base', {
            fullSelector: '#main > .search-container',
            template: 'scheduled-job/cronjob',
        });
    }

    afterRender() {
        super.afterRender();

        Espo.Ajax
            .getRequest('Admin/action/cronMessage')
            .then(data => {
                this.$el.find('.cronjob .message').html(data.message);
                this.$el.find('.cronjob .command').html('<strong>' + data.command + '</strong>');
            });
    }

    getHeader() {
        return this.buildHeaderHtml([
            $('<a>')
                .attr('href', '#Admin')
                .text(this.translate('Administration', 'labels', 'Admin')),
            this.getLanguage().translate(this.scope, 'scopeNamesPlural')
        ]);
    }
}

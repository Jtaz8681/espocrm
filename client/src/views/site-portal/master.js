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

import MasterSiteView from 'views/site/master';

export default class extends MasterSiteView {

    views = {
        header: {
            id: 'header',
            view: 'views/site-portal/header'
        },
        main: {
            id: 'main',
            view: false,
        },
        footer: {
            fullSelector: 'body > footer',
            view: 'views/site/footer'
        }
    }

    afterRender() {
        super.afterRender();

        this.element.querySelector('#main').classList.add('main-portal');

        //this.$el.find('#main').addClass('main-portal');
    }
}

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

import View from 'view';

class AboutView extends View {

    template = 'about'

    data() {
        return {
            version: this.version,
            text: this.getHelper().transformMarkdownText(this.text)
        };
    }

    setup() {
        this.wait(
            Espo.Ajax.getRequest('App/about')
                .then(data => {
                    this.text = data.text;
                    this.version = data.version;
                })
        );
    }
}

export default AboutView;

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

import ThemeSettingsFieldView from 'views/settings/fields/theme';

export default class extends ThemeSettingsFieldView {

    setupOptions() {
        this.params.options = Object.keys(this.getMetadata().get('themes') || {})
            .sort((v1, v2) => {
                if (v2 === 'EspoRtl') {
                    return -1;
                }

                return this.translate(v1, 'themes').localeCompare(this.translate(v2, 'themes'));
            });

        this.params.options.unshift('');
    }

    setupTranslation() {
        super.setupTranslation();

        this.translatedOptions = this.translatedOptions || {};

        const defaultTheme = this.getConfig().get('theme');
        const defaultTranslated = this.translatedOptions[defaultTheme] || defaultTheme;

        this.translatedOptions[''] = `${this.translate('Default')} · ${defaultTranslated}`;
    }

    afterRenderDetail() {
        const navbar = this.getNavbarValue() || this.getDefaultNavbar();

        if (navbar) {
            this.$el
                .append(' ')
                .append(
                    $('<span>').addClass('text-muted chevron-right')
                )
                .append(' ')
                .append(
                    $('<span>').text(this.translate(navbar, 'themeNavbars'))
                )
        }
    }
}

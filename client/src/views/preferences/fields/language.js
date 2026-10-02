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

import EnumFieldView from 'views/fields/enum';

export default class extends EnumFieldView {

    setupOptions() {
        this.params.options =
            Espo.Utils.clone(this.getMetadata().get(['app', 'language', 'list']) || [])
                .sort((v1, v2) => {
                    return this.getLanguage().translateOption(v1, 'language')
                        .localeCompare(this.getLanguage().translateOption(v2, 'language'));
                });

        this.params.options.unshift('');

        this.translatedOptions = Espo.Utils.clone(this.getLanguage().translate('language', 'options') || {});

        const defaultTranslated = this.translatedOptions[this.getConfig().get('language')] ||
            this.getConfig().get('language');

        this.translatedOptions[''] = `${this.translate('Default')} · ${defaultTranslated}`;
    }
}

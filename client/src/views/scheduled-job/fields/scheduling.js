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

import VarcharFieldView from 'views/fields/varchar';

export default class extends VarcharFieldView {

    setup() {
        super.setup();

        if (this.isEditMode() || this.isDetailMode()) {
            this.wait(
                Espo.loader.requirePromise('lib!cronstrue')
                    .then(Cronstrue => {
                        this.Cronstrue = Cronstrue;

                        this.listenTo(this.model, 'change:' + this.name, () => this.showText());
                    })
            );
        }
    }

    afterRender() {
        super.afterRender();

        if (this.isEditMode() || this.isDetailMode()) {
            const $text = this.$text = $('<div class="small text-success"/>');

            this.$el.append($text);
            this.showText();
        }
    }

    /**
     * @private
     */
    showText() {
        let text;
        if (!this.$text || !this.$text.length) {
            return;
        }

        if (!this.Cronstrue) {
            return;
        }

        const exp = this.model.get(this.name);

        if (!exp) {
            this.$text.text('');

            return;
        }

        if (exp === '* * * * *') {
            this.$text.text(this.translate('As often as possible', 'labels', 'ScheduledJob'));

            return;
        }

        let locale = 'en';
        const localeList = Object.keys(this.Cronstrue.default.locales);
        const language = this.getLanguage().name;

        if (~localeList.indexOf(language)) {
            locale = language;
        } else if (~localeList.indexOf(language.split('_')[0])) {
            locale = language.split('_')[0];
        }

        try {
            text = this.Cronstrue.toString(exp, {
                use24HourTimeFormat: !this.getDateTime().hasMeridian(),
                locale: locale,
            });
        } catch (e) {
            text = this.translate('Not valid');
        }

        this.$text.text(text);
    }
}

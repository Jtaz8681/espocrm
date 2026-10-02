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
import ThemeManager from 'theme-manager';
import Select from 'ui/select';

export default class ThemeSettingsFieldView extends EnumFieldView {

    // language=Handlebars
    editTemplateContent = `
        <div class="grid-auto-fit-xxs">
            <div>
                <select data-name="{{name}}" class="form-control main-element">
                    {{options
                        params.options value
                        scope=scope
                        field=name
                        translatedOptions=translatedOptions
                        includeMissingOption=true
                        styleMap=params.style
                    }}
                </select>
            </div>
            {{#if navbarOptionList.length}}
            <div>
                <select data-name="themeNavbar" class="form-control">
                    {{options navbarOptionList navbar translatedOptions=navbarTranslatedOptions}}
                </select>
            </div>
            {{/if}}
        </div>
    `

    data() {
        const data = super.data();

        data.navbarOptionList = this.getNavbarOptionList();
        data.navbar = this.getNavbarValue() || this.getDefaultNavbar();

        data.navbarTranslatedOptions = {};
        data.navbarOptionList.forEach(item => {
            data.navbarTranslatedOptions[item] = this.translate(item, 'themeNavbars');
        });

        return data;
    }

    setup () {
        super.setup();

        this.initThemeManager();

        this.model.on('change:theme', (m, v, o) => {
            this.initThemeManager()

            if (o.ui) {
                this.reRender()
                    .then(() => Select.focus(this.$element, {noTrigger: true}));
            }
        })
    }

    afterRenderEdit() {
        this.$navbar = this.$el.find('[data-name="themeNavbar"]');

        this.$navbar.on('change', () => this.trigger('change'));

        Select.init(this.$navbar);
    }

    /**
     * @protected
     * @return {string}
     */
    getNavbarValue() {
        const params = this.model.get('themeParams') || {};

        return params.navbar;
    }

    /**
     * @protected
     * @return {Record|null}
     */
    getNavbarDefs() {
        if (!this.themeManager) {
            return null;
        }

        const params = this.themeManager.getParam('params');

        if (!params || !params.navbar) {
            return null;
        }

        return Espo.Utils.cloneDeep(params.navbar);
    }

    /**
     * @private
     * @return {string[]}
     */
    getNavbarOptionList() {
        const defs = this.getNavbarDefs();

        if (!defs) {
            return [];
        }

        const optionList = defs.options || [];

        if (!optionList.length || optionList.length === 1) {
            return [];
        }

        return optionList;
    }

    /**
     * @protected
     * @return {string|null}
     */
    getDefaultNavbar() {
        const defs = this.getNavbarDefs() || {};

        return defs.default || null;
    }

    /**
     * @private
     */
    initThemeManager() {
        const theme = this.model.get('theme');

        if (!theme) {
            this.themeManager = null;

            return;
        }

        this.themeManager = new ThemeManager(
            this.getConfig(),
            this.getPreferences(),
            this.getMetadata(),
            theme
        );
    }

    getAttributeList() {
        return [this.name, 'themeParams'];
    }

    setupOptions() {
        this.params.options = Object.keys(this.getMetadata().get('themes') || {})
            .sort((v1, v2) => {
                if (v2 === 'EspoRtl') {
                    return -1;
                }

                return this.translate(v1, 'theme')
                    .localeCompare(this.translate(v2, 'theme'));
            });
    }

    fetch() {
        const data = super.fetch();

        const params = {};

        if (this.$navbar.length) {
            params.navbar = this.$navbar.val();
        }

        data.themeParams = params;

        return data;
    }
}

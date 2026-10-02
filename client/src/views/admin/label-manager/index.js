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
import Select from 'ui/select';

class LabelManagerView extends  View {

    template = 'admin/label-manager/index'

    scopeList = null
    scope = null
    language = null
    languageList = null

    events = {
        /** @this LabelManagerView */
        'click [data-action="selectScope"]': function (e) {
            const scope = $(e.currentTarget).data('name');

            this.getRouter().checkConfirmLeaveOut(() => {
                this.selectScope(scope);
            });
        },
        /** @this LabelManagerView */
        'change select[data-name="language"]': function (e) {
            const language = $(e.currentTarget).val();

            this.getRouter().checkConfirmLeaveOut(() => {
                this.selectLanguage(language);
            });
        }
    }

    data() {
        return {
            scopeList: this.scopeList,
            languageList: this.languageList,
            scope: this.scope,
            language: this.language,
        };
    }

    setup() {
        this.languageList = this.getMetadata().get(['app', 'language', 'list']) || ['en_US'];

        this.languageList.sort((v1, v2) => {
            return this.getLanguage().translateOption(v1, 'language')
                .localeCompare(this.getLanguage().translateOption(v2, 'language'));
        });

        this.wait(true);

        Espo.Ajax.postRequest('LabelManager/action/getScopeList').then(scopeList => {
            this.scopeList = scopeList;

            this.scopeList.sort((v1, v2) => {
                return this.translate(v1, 'scopeNamesPlural')
                    .localeCompare(this.translate(v2, 'scopeNamesPlural'));
            });

            this.scopeList = this.scopeList.filter(scope => {
                if (scope === 'Global') {
                    return;
                }

                if (this.getMetadata().get(['scopes', scope])) {
                    if (this.getMetadata().get(['scopes', scope, 'disabled'])) {
                        return;
                    }
                }

                return true;
            });

            this.scopeList.unshift('Global');

            this.wait(false);
        });

        this.scope = this.options.scope || 'Global';
        this.language = this.options.language || this.getConfig().get('language');

        this.once('after:render', () => {
            this.selectScope(this.scope, true);
        });
    }

    afterRender() {
        Select.init(
            this.element.querySelector(`select[data-name="language"]`)
        );
    }

    selectLanguage(language) {
        this.language = language;

        if (this.scope) {
            this.getRouter().navigate(
                '#Admin/labelManager/scope=' + this.scope + '&language=' + this.language,
                {trigger: false}
            );
        } else {
            this.getRouter().navigate('#Admin/labelManager/language=' + this.language, {trigger: false});
        }

        this.createRecordView();
    }

    selectScope(scope, skipRouter) {
        this.scope = scope;

        if (!skipRouter) {
            this.getRouter().navigate('#Admin/labelManager/scope=' + scope + '&language=' + this.language,
                {trigger: false});
        }

        this.$el.find('[data-action="selectScope"]')
            .removeClass('disabled')
            .removeAttr('disabled');

        this.$el.find('[data-name="' + scope + '"][data-action="selectScope"]')
            .addClass('disabled')
            .attr('disabled', 'disabled');

        this.createRecordView();
    }

    createRecordView() {
        Espo.Ui.notifyWait();

        this.createView('record', 'views/admin/label-manager/edit', {
            selector: '.language-record',
            scope: this.scope,
            language: this.language,
        }, view => {
            view.render();

            Espo.Ui.notify(false);

            $(window).scrollTop(0);
        });
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Label Manager', 'labels', 'Admin'));
    }
}

export default LabelManagerView;

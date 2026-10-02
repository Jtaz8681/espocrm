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

export default class TemplateManagerIndexView extends View {

    template = 'admin/template-manager/index'

    data() {
        return {
            templateDataList: this.templateDataList,
        };
    }

    events = {
        /** @this TemplateManagerIndexView */
        'click [data-action="selectTemplate"]': function (e) {
            const name = $(e.currentTarget).data('name');

            this.getRouter().checkConfirmLeaveOut(() => {
                this.selectTemplate(name);
            });
        }
    }

    setup() {
        this.templateDataList = [];

        const templateList = Object.keys(this.getMetadata().get(['app', 'templates']) || {});

        templateList.sort((v1, v2) => {
            return this.translate(v1, 'templates', 'Admin')
                .localeCompare(this.translate(v2, 'templates', 'Admin'));
        });

        templateList.forEach(template =>{
            const defs = /** @type {Record} */
                this.getMetadata().get(['app', 'templates', template]);

            if (defs.scopeListConfigParam || defs.scopeList) {
                const scopeList = Espo.Utils
                    .clone(defs.scopeList || this.getConfig().get(defs.scopeListConfigParam) || []);

                scopeList.sort((v1, v2) => {
                    return this.translate(v1, 'scopeNames')
                        .localeCompare(this.translate(v2, 'scopeNames'));
                });

                scopeList.forEach(scope => {
                    const o = {
                        name: `${template}_${scope}`,
                        text: this.translate(template, 'templates', 'Admin') + ' · ' +
                            this.translate(scope, 'scopeNames'),
                    };

                    this.templateDataList.push(o);
                });

                return;
            }

            const o = {
                name: template,
                text: this.translate(template, 'templates', 'Admin'),
            };

            this.templateDataList.push(o);
        });

        this.selectedTemplate = this.options.name;

        if (this.selectedTemplate) {
            this.once('after:render', () => {
                this.selectTemplate(this.selectedTemplate, true);
            });
        }
    }

    selectTemplate(name) {
        this.selectedTemplate = name;

        this.getRouter().navigate('#Admin/templateManager/name=' + this.selectedTemplate, {trigger: false});

        this.createRecordView();

        this.$el.find('[data-action="selectTemplate"]')
            .removeClass('disabled')
            .removeAttr('disabled');

        this.$el.find(`[data-name="${name}"][data-action="selectTemplate"]`)
            .addClass('disabled')
            .attr('disabled', 'disabled');
    }

    createRecordView() {
        Espo.Ui.notifyWait();

        this.createView('record', 'views/admin/template-manager/edit', {
            selector: '.template-record',
            name: this.selectedTemplate,
        }, (view) => {
            view.render();

            Espo.Ui.notify(false);
            $(window).scrollTop(0);
        });
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Template Manager', 'labels', 'Admin'));
    }
}

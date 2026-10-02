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
import Model from 'model';

export default class TemplateManagerEditView extends View {

    template = 'admin/template-manager/edit'

    data() {
        return {
            title: this.title,
            hasSubject: this.hasSubject
        };
    }

    events = {
        /** @this TemplateManagerEditView */
        'click [data-action="save"]': function () {
            this.actionSave();
        },
        /** @this TemplateManagerEditView */
        'click [data-action="cancel"]': function () {
            this.actionCancel();
        },
        /** @this TemplateManagerEditView */
        'click [data-action="resetToDefault"]': function () {
            this.actionResetToDefault();
        },
        /** @this TemplateManagerEditView */
        'keydown.form': function (e) {
            const key = Espo.Utils.getKeyFromKeyEvent(e);

            if (key === 'Control+KeyS' || key === 'Control+Enter') {
                this.actionSave();

                e.preventDefault();
                e.stopPropagation();
            }
        },
    }

    setup() {
        this.wait(true);

        this.fullName = this.options.name;

        this.name = this.fullName;
        this.scope = null;

        const arr = this.fullName.split('_');

        if (arr.length > 1) {
            this.scope = arr[1];
            this.name = arr[0];
        }

        this.hasSubject = !this.getMetadata().get(['app', 'templates', this.name, 'noSubject']);

        this.title = this.translate(this.name, 'templates', 'Admin');
        if (this.scope) {
            this.title += ' · ' + this.translate(this.scope, 'scopeNames');
        }

        this.attributes = {};

        Espo.Ajax.getRequest('TemplateManager/action/getTemplate', {
            name: this.name,
            scope: this.scope
        }).then(data => {
            const model = this.model = new Model();

            model.name = 'TemplateManager';
            model.set('body', data.body);
            this.attributes.body = data.body;

            if (this.hasSubject) {
                model.set('subject', data.subject);
                this.attributes.subject = data.subject;
             }

            this.listenTo(model, 'change', () => {
                this.setConfirmLeaveOut(true);
            });

            this.createView('bodyField', 'views/admin/template-manager/fields/body', {
                name: 'body',
                model: model,
                selector: '.body-field',
                mode: 'edit'
            });

            if (this.hasSubject) {
                this.createView('subjectField', 'views/fields/varchar', {
                    name: 'subject',
                    model: model,
                    selector: '.subject-field',
                    mode: 'edit'
                });
            }

            this.wait(false);
        });
    }

    setConfirmLeaveOut(value) {
        this.getRouter().confirmLeaveOut = value;
    }

    afterRender() {
        this.$save = this.$el.find('button[data-action="save"]');
        this.$cancel = this.$el.find('button[data-action="cancel"]');
        this.$resetToDefault = this.$el.find('button[data-action="resetToDefault"]');
    }

    actionSave() {
        this.$save.addClass('disabled').attr('disabled');
        this.$cancel.addClass('disabled').attr('disabled');
        this.$resetToDefault.addClass('disabled').attr('disabled');

        const bodyFieldView = /** @type {import('views/fields/base').default} */
            this.getView('bodyField');

        bodyFieldView.fetchToModel();

        const data = {
            name: this.name,
            body: this.model.get('body'),
        };

        if (this.scope) {
            data.scope = this.scope;
        }

        if (this.hasSubject) {
            const subjectFieldView = /** @type {import('views/fields/base').default} */
                this.getView('subjectField');

            subjectFieldView.fetchToModel();

            data.subject = this.model.get('subject');
        }

        Espo.Ui.notify(this.translate('saving', 'messages'));

        Espo.Ajax.postRequest('TemplateManager/action/saveTemplate', data)
            .then(() => {
                this.setConfirmLeaveOut(false);

                this.attributes.body = data.body;
                this.attributes.subject = data.subject;

                this.$save.removeClass('disabled').removeAttr('disabled');
                this.$cancel.removeClass('disabled').removeAttr('disabled');
                this.$resetToDefault.removeClass('disabled').removeAttr('disabled');

                Espo.Ui.success(this.translate('Saved'));
            })
            .catch(() => {
                this.$save.removeClass('disabled').removeAttr('disabled');
                this.$cancel.removeClass('disabled').removeAttr('disabled');
                this.$resetToDefault.removeClass('disabled').removeAttr('disabled');
            });
    }

    actionCancel() {
        this.model.set('subject', this.attributes.subject);
        this.model.set('body', this.attributes.body);

        this.setConfirmLeaveOut(false);
    }

    actionResetToDefault() {
        this.confirm(this.translate('confirmation', 'messages'), () => {
            this.$save.addClass('disabled').attr('disabled');
            this.$cancel.addClass('disabled').attr('disabled');
            this.$resetToDefault.addClass('disabled').attr('disabled');

            const data = {
                name: this.name,
                body: this.model.get('body'),
            };

            if (this.scope) {
                data.scope = this.scope;
            }

            Espo.Ui.notifyWait();

            Espo.Ajax.postRequest('TemplateManager/action/resetTemplate', data)
                .then(returnData => {
                    this.$save.removeClass('disabled').removeAttr('disabled');
                    this.$cancel.removeClass('disabled').removeAttr('disabled');
                    this.$resetToDefault.removeClass('disabled').removeAttr('disabled');

                    this.attributes.body = returnData.body;
                    this.attributes.subject = returnData.subject;

                    this.model.set('subject', returnData.subject);
                    this.model.set('body', returnData.body);
                    this.setConfirmLeaveOut(false);

                    Espo.Ui.notify(false);
                })
                .catch(() => {
                    this.$save.removeClass('disabled').removeAttr('disabled');
                    this.$cancel.removeClass('disabled').removeAttr('disabled');
                    this.$resetToDefault.removeClass('disabled').removeAttr('disabled');
                });
        });
    }
}

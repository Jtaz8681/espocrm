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

export default class IntegrationsEditView extends View {

    template = 'admin/integrations/edit'

    /**
     * @protected
     * @type {string}
     */
    integration

    /**
     * @private
     * @type {string|null}
     */
    helpText = null

    /**
     * @private
     * @type {{name: string, label: string}[]}
     */
    fieldDataList

    /**
     * @private
     * @type {string[]}
     */
    fieldList

    data() {
        return {
            integration: this.integration,
            fieldDataList: this.fieldDataList,
            helpText: this.helpText,
        };
    }

    setup() {
        this.addActionHandler('save', () => this.save());
        this.addActionHandler('cancel', () => this.actionCancel());

        this.integration = this.options.integration;

        if (this.getLanguage().has(this.integration, 'help', 'Integration')) {
            this.helpText = this.translate(this.integration, 'help', 'Integration');
        }

        this.fieldList = [];
        this.fieldDataList = [];

        this.model = new Model({}, {
            entityType: 'Integration',
            urlRoot: 'Integration',
        });

        this.model.id = this.integration;

        const fieldDefs = {
            enabled: {
                required: true,
                type: 'bool',
            },
        };

        const fields = /** @type {Record<string, Record>} */
            this.getMetadata().get(`integrations.${this.integration}.fields`) ?? {};

        Object.keys(fields).forEach(name => {
            const defs = {...fields[name]};

            fieldDefs[name] = defs;

            let label = this.translate(name, 'fields', 'Integration');

            if (defs.labelTranslation) {
                label = this.getLanguage().translatePath(defs.labelTranslation);
            }

            this.fieldDataList.push({
                name: name,
                label: label,
            });
        });

        this.model.setDefs({fields: fieldDefs});
        this.model.populateDefaults();

        this.wait(
            (async () => {
                await this.model.fetch();

                this.createFieldView('bool', 'enabled');

                Object.keys(fields).forEach(name => {
                    this.createFieldView(fields[name].type, name, undefined, fields[name]);
                });
            })()
        );
    }

    /**
     * @private
     */
    actionCancel() {
        this.getRouter().navigate('#Admin/integrations', {trigger: true});
    }

    /**
     * @protected
     * @param {string} name
     */
    hideField(name) {
        this.$el.find('label[data-name="' + name + '"]').addClass('hide');
        this.$el.find('div.field[data-name="' + name + '"]').addClass('hide');

        const view = this.getView(name);

        if (view) {
            view.disabled = true;
        }
    }

    /**
     * @protected
     * @param {string} name
     */
    showField(name) {
        this.$el.find(`label[data-name="${name}"]`).removeClass('hide');
        this.$el.find(`div.field[data-name="${name}"]`).removeClass('hide');

        const view = this.getFieldView(name);

        if (view) {
            view.disabled = false;
        }
    }

    /**
     * @since 9.0.0
     * @param {string} name
     * @return {import('views/fields/base').default}
     */
    getFieldView(name) {
        return this.getView(name)
    }

    afterRender() {
        if (!this.model.attributes.enabled) {
            this.fieldDataList.forEach(it => this.hideField(it.name));
        }

        this.listenTo(this.model, 'change:enabled', () => {
            if (this.model.attributes.enabled) {
                this.fieldDataList.forEach(it => this.showField(it.name));
            } else {
                this.fieldDataList.forEach(it => this.hideField(it.name));
            }
        });
    }

    /**
     * @protected
     * @param {string} type
     * @param {string} name
     * @param {boolean} [readOnly]
     * @param {Record} [params]
     */
    createFieldView(type, name, readOnly, params) {
        const viewName = this.model.getFieldParam(name, 'view') || this.getFieldManager().getViewName(type);

        let labelText = undefined;

        if (params && params.labelTranslation) {
            labelText = this.getLanguage().translatePath(params.labelTranslation);
        }

        this.createView(name, viewName, {
            name: name,
            model: this.model,
            selector: `.field[data-name="${name}"]`,
            params: params,
            mode: readOnly ? 'detail' : 'edit',
            readOnly: readOnly,
            labelText: labelText,
        });

        this.fieldList.push(name);
    }

    /**
     * @protected
     */
    save() {
        this.fieldList.forEach(field => {
            const view = this.getFieldView(field);

            if (!view.readOnly) {
                view.fetchToModel();
            }
        });

        let notValid = false;

        this.fieldList.forEach(field => {
            const fieldView = this.getFieldView(field);

            if (fieldView && !fieldView.disabled) {
                notValid = fieldView.validate() || notValid;
            }
        });

        if (notValid) {
            Espo.Ui.error(this.translate('Not valid'));

            return;
        }

        this.listenToOnce(this.model, 'sync', () => {
            Espo.Ui.success(this.translate('Saved'));
        });

        Espo.Ui.notify(this.translate('saving', 'messages'));

        this.model.save();
    }
}

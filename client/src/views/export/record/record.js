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

import EditForModalRecordView from 'views/record/edit-for-modal';

class ExportRecordView extends EditForModalRecordView {

    formatList = null

    /**
     * @type {Object.<string, string[]>},
     */
    customParams = null


    setupBeforeFinal() {
        this.formatList = this.options.formatList;
        this.scope = this.options.scope;

        const fieldsData = this.getExportFieldsData();

        this.setupExportFieldDefs(fieldsData);
        this.setupExportLayout(fieldsData);
        this.setupExportDynamicLogic();

        this.controlFormatField();
        this.listenTo(this.model, 'change:format', () => this.controlFormatField());

        this.controlAllFields();
        this.listenTo(this.model, 'change:exportAllFields', () => this.controlAllFields());

        super.setupBeforeFinal();
    }

    setupExportFieldDefs(fieldsData) {
        const fieldDefs = {
            format: {
                type: 'enum',
                options: this.formatList,
            },
            fieldList: {
                type: 'multiEnum',
                options: fieldsData.list,
                required: true,
            },
            exportAllFields: {
                type: 'bool',
            },
        };

        this.customParams = {};

        this.formatList.forEach(format => {
            const fields = this.getFormatParamsDefs(format).fields || {};

            this.customParams[format] = [];

            for (const name in fields) {
                const newName = this.modifyParamName(format, name);

                this.customParams[format].push(name);

                fieldDefs[newName] = Espo.Utils.cloneDeep(fields[name]);
            }
        });

        this.model.setDefs({fields: fieldDefs});
    }

    setupExportLayout(fieldsData) {
        this.detailLayout = [];

        const mainPanel = {
            rows: [
                [
                    {name: 'format'},
                    false
                ],
                [
                    {name: 'exportAllFields'},
                    false
                ],
                [
                    {
                        name: 'fieldList',
                        options: {
                            translatedOptions: fieldsData.translations,
                        },
                    }
                ],
            ]
        };

        this.detailLayout.push(mainPanel);

        this.formatList.forEach(format => {
            const rows = this.getFormatParamsDefs(format).layout || [];

            rows.forEach(row => {
                row.forEach(item => {
                    item.name = this.modifyParamName(format, item.name);
                });
            })

            this.detailLayout.push({
                name: format,
                rows: rows,
            })
        });
    }

    setupExportDynamicLogic() {
        this.dynamicLogicDefs = {
            fields: {},
        };

        this.formatList.forEach(format => {
            const defs = this.getFormatParamsDefs(format).dynamicLogic || {};

            this.customParams[format].forEach(param => {
                const logic = defs[param] || {};

                if (!logic.visible) {
                    logic.visible = {};
                }

                if (!logic.visible.conditionGroup) {
                    logic.visible.conditionGroup = [];
                }

                logic.visible.conditionGroup.push({
                    type: 'equals',
                    attribute: 'format',
                    value: format,
                });

                const newName = this.modifyParamName(format, param);

                this.dynamicLogicDefs.fields[newName] = logic;
            });
        });
    }

    /**
     * @param {string} format
     * @return {string[]}
     */
    getFormatParamList(format) {
        return Object.keys(this.getFormatParamsDefs(format).fields || {});
    }

    /**
     * @private
     * @return {Object.<string, *>}
     */
    getFormatParamsDefs(format) {
        const defs = this.getMetadata().get(['app', 'export', 'formatDefs', format]) || {};

        return Espo.Utils.cloneDeep(defs.params || {});
    }

    /**
     * @param {string} format
     * @param {string} name
     * @return {string}
     */
    modifyParamName(format, name) {
        return format + Espo.Utils.upperCaseFirst(name);
    }

    /**
     * @return {{
     *   translations: Object.<string, string>,
     *   list: string[]
     * }}
     */
    getExportFieldsData() {
        let fieldList = this.getFieldManager().getEntityTypeFieldList(this.scope);
        const forbiddenFieldList = this.getAcl().getScopeForbiddenFieldList(this.scope);

        fieldList = fieldList.filter(item => {
            return !~forbiddenFieldList.indexOf(item);
        });

        fieldList = fieldList.filter(item => {
            /** @type {Record} */
            const defs = this.getMetadata().get(['entityDefs', this.scope, 'fields', item]) || {};

            if (
                defs.disabled ||
                defs.exportDisabled ||
                defs.type === 'map' ||
                defs.utility
            ) {
                return false
            }

            return true;
        });

        this.getLanguage().sortFieldList(this.scope, fieldList);

        fieldList.unshift('id');

        const fieldListTranslations = {};

        fieldList.forEach(item => {
            fieldListTranslations[item] = this.getLanguage().translate(item, 'fields', this.scope);
        });

        const setFieldList = this.model.get('fieldList') || [];

        setFieldList.forEach(item => {
            if (~fieldList.indexOf(item)) {
                return;
            }

            if (!~item.indexOf('_')) {
                return;
            }

            const arr = item.split('_');

            fieldList.push(item);

            const foreignScope = this.getMetadata().get(['entityDefs', this.scope, 'links', arr[0], 'entity']);

            if (!foreignScope) {
                return;
            }

            fieldListTranslations[item] = this.getLanguage().translate(arr[0], 'links', this.scope) + '.' +
                this.getLanguage().translate(arr[1], 'fields', foreignScope);
        });

        return {
            list: fieldList,
            translations: fieldListTranslations,
        };
    }

    controlAllFields() {
        if (!this.model.get('exportAllFields')) {
            this.showField('fieldList');

            return;
        }

        this.hideField('fieldList');
    }

    controlFormatField() {
        const format = this.model.get('format');

        this.formatList
            .filter(item => item !== format)
            .forEach(format => {
                this.hidePanel(format);
            });

        this.formatList
            .filter(item => item === format)
            .forEach(format => {
                this.customParams[format].length ?
                    this.showPanel(format) :
                    this.hidePanel(format);
            });
    }
}

export default ExportRecordView;

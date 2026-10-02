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

import ModalView from 'views/modal';
import Model from 'model';

class ExportModalView extends ModalView {

    cssName = 'export-modal'
    className = 'dialog dialog-record'
    template = 'export/modals/export'

    shortcutKeys = {
        'Control+Enter': 'export'
    }

    setup() {
        this.buttonList = [
            {
                name: 'export',
                label: 'Export',
                style: 'danger',
                title: 'Ctrl+Enter',
            },
            {
                name: 'cancel',
                label: 'Cancel',
            }
        ];

        this.model = new Model();
        this.model.name = 'Export';

        this.scope = this.options.scope;

        if (this.options.fieldList) {
            const fieldList = this.options.fieldList
                .filter(field => {
                    /** @type {Record} */
                    const defs = this.getMetadata().get(`entityDefs.${this.scope}.fields.${field}`) || {};

                    return !defs.exportDisabled && !defs.utility;
                });

            this.model.set('fieldList', fieldList);
            this.model.set('exportAllFields', false);
        } else {
            this.model.set('exportAllFields', true);
        }

        const formatList =
            this.getMetadata().get(['scopes', this.scope, 'exportFormatList']) ||
            this.getMetadata().get('app.export.formatList');

        this.model.set('format', formatList[0]);

        this.createView('record', 'views/export/record/record', {
            scope: this.scope,
            model: this.model,
            selector: '.record',
            formatList: formatList,
        });
    }

    /**
     * @return {import('views/record/edit').default}
     */
    getRecordView() {
        return this.getView('record');
    }

    // noinspection JSUnusedGlobalSymbols
    actionExport() {
        const recordView = this.getRecordView();

        const data = recordView.fetch();

        this.model.set(data);

        if (recordView.validate()) {
            return;
        }

        const returnData = {
            exportAllFields: data.exportAllFields,
            format: data.format,
        };

        if (!data.exportAllFields) {
            const attributeList = [];

            data.fieldList.forEach(item => {
                if (item === 'id') {
                    attributeList.push('id');

                    return;
                }

                const type = this.getMetadata().get(['entityDefs', this.scope, 'fields', item, 'type']);

                if (type) {
                    this.getFieldManager().getAttributeList(type, item)
                        .forEach(attribute => {
                            attributeList.push(attribute);
                        });
                }

                if (~item.indexOf('_')) {
                    attributeList.push(item);
                }
            });

            returnData.attributeList = attributeList;
            returnData.fieldList = data.fieldList;
        }

        returnData.params = {};

        recordView.getFormatParamList(data.format).forEach(param => {
            const name = recordView.modifyParamName(data.format, param);

            const fieldView = recordView.getFieldView(name);

            if (!fieldView || fieldView.disabled) {
                return;
            }

            this.getFieldManager()
                .getActualAttributeList(fieldView.type, param)
                .forEach(subParam => {
                    const name = recordView.modifyParamName(data.format, subParam);

                    returnData.params[subParam] = data[name];
                });
        });

        this.trigger('proceed', returnData);
        this.close();
    }
}

export default ExportModalView;

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

import LayoutRowsView from 'views/admin/layouts/rows';

class LayoutMassUpdateView extends LayoutRowsView {

    dataAttributeList = ['name']
    editable = false
    ignoreList = []
    ignoreTypeList = ['duration']

    dataAttributesDefs = {
        name: {
            readOnly: true
        },
    }

    setup() {
        super.setup();

        this.wait(true);

        this.loadLayout(() => {
            this.wait(false);
        });
    }

    loadLayout(callback) {
        this.getModelFactory().create(this.scope).then(model => {
            this.getHelper().layoutManager.getOriginal(this.scope, this.type, this.setId, (layout) => {
                const allFields = [];

                for (const field in model.defs.fields) {
                    if (
                        !model.getFieldParam(field, 'massUpdateDisabled') &&
                        !model.getFieldParam(field, 'readOnly') &&
                        !model.getFieldParam(field, 'readOnlyAfterCreate') &&
                        this.isFieldEnabled(model, field) &&
                        model.getFieldType('field') !== 'foreign'
                    ) {
                        allFields.push(field);
                    }
                }

                allFields.sort((v1, v2) => {
                    return this.translate(v1, 'fields', this.scope)
                        .localeCompare(this.translate(v2, 'fields', this.scope));
                });

                this.enabledFieldsList = [];

                this.enabledFields = [];
                this.disabledFields = [];

                for (const i in layout) {
                    this.enabledFields.push({
                        name: layout[i],
                        labelText: this.getLanguage().translate(layout[i], 'fields', this.scope),
                    });

                    this.enabledFieldsList.push(layout[i]);
                }

                for (const i in allFields) {
                    if (!_.contains(this.enabledFieldsList, allFields[i])) {
                        this.disabledFields.push({
                            name: allFields[i],
                            labelText: this.getLanguage().translate(allFields[i], 'fields', this.scope),
                        });
                    }
                }

                this.rowLayout = this.enabledFields;

                for (const i in this.rowLayout) {
                    this.rowLayout[i].labelText = this.getLanguage()
                        .translate(this.rowLayout[i].name, 'fields', this.scope);

                    this.itemsData[this.rowLayout[i].name] = Espo.Utils.cloneDeep(this.rowLayout[i]);
                }

                callback();
            });
        });
    }

    fetch() {
        const layout = [];

        $("#layout ul.enabled > li").each((i, el) => {
            layout.push($(el).data('name'));
        });

        return layout;
    }

    validate() {
        return true;
    }

    isFieldEnabled(model, name) {
        if (this.ignoreList.indexOf(name) !== -1) {
            return false;
        }

        if (this.ignoreTypeList.indexOf(model.getFieldParam(name, 'type')) !== -1) {
            return false;
        }

        const layoutList = model.getFieldParam(name, 'layoutAvailabilityList');

        if (layoutList && !layoutList.includes(this.type)) {
            return;
        }

        const layoutIgnoreList = model.getFieldParam(name, 'layoutIgnoreList') || [];

        if (layoutIgnoreList.includes(this.type)) {
            return false;
        }

        return !model.getFieldParam(name, 'disabled') &&
            !model.getFieldParam(name, 'utility') &&
            !model.getFieldParam(name, 'layoutMassUpdateDisabled') &&
            !model.getFieldParam(name, 'readOnly');
    }
}

export default LayoutMassUpdateView;

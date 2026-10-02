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

class LayoutFiltersView extends LayoutRowsView {

    dataAttributeList = ['name']
    editable = false
    ignoreList = []

    setup() {
        super.setup();

        this.wait(true);

        this.loadLayout(() => this.wait(false));
    }

    loadLayout(callback) {
        this.getModelFactory().create(this.scope, model => {
            this.getHelper().layoutManager.getOriginal(this.scope, this.type, this.setId, layout => {
                const allFields = [];

                for (const field in model.defs.fields) {
                    if (
                        this.checkFieldType(model.getFieldParam(field, 'type')) &&
                        this.isFieldEnabled(model, field)
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

                for (const item of layout) {
                    this.enabledFields.push({
                        name: item,
                        labelText: this.getLanguage().translate(item, 'fields', this.scope)
                    });

                    this.enabledFieldsList.push(item);
                }

                for (const item of allFields) {
                    if (!this.enabledFieldsList.includes(item)) {
                        this.disabledFields.push({
                            name: item,
                            labelText: this.getLanguage().translate(item, 'fields', this.scope)
                        });
                    }
                }

                /** @type {Object[]} */
                this.rowLayout = this.enabledFields;

                for (const item of this.rowLayout) {
                    item.labelText = this.getLanguage().translate(item.name, 'fields', this.scope);
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

    checkFieldType(type) {
        return this.getFieldManager().checkFilter(type);
    }

    validate() {
        return true;
    }

    isFieldEnabled(model, name) {
        if (this.ignoreList.indexOf(name) !== -1) {
            return false;
        }

        /** @type {string[]|null} */
        const layoutList = model.getFieldParam(name, 'layoutAvailabilityList');

        if (
            layoutList &&
            !layoutList.includes(this.type)
        ) {
            return false;
        }

        return !model.getFieldParam(name, 'disabled') &&
            !model.getFieldParam(name, 'utility') &&
            !model.getFieldParam(name, 'layoutFiltersDisabled');
    }
}

export default LayoutFiltersView;

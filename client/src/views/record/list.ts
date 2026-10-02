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

import ListBaseRecordView, {ListBaseRecordViewOptions, ListBaseRecordViewSchema} from 'views/record/list-base';
import Model from 'model';

/**
 * Mass-action definitions.
 */
export interface MassActionItem {
    /**
     * A name.
     */
    name?: string;
    /**
     * A group index.
     */
    groupIndex?: number;
    /**
     * A handler.
     */
    handler?: string;
    /**
     * An init function.
     */
    initFunction?: string;
    /**
     * An action function.
     */
    actionFunction?: string;
    /**
     * A config check.
     */
    configCheck?: string;
    /**
     * An ACL scope to check.
     */
    aclScope?: string;
    /**
     * An access action to check.
     */
    acl?: 'create' | 'read' | 'edit' | 'stream' | 'delete' | null;
    /**
     * A URL.
     */
    url?: string;
    /**
     * To skip confirmation.
     */
    bypassConfirmation?: boolean;
    /**
     * A confirmation message.
     */
    confirmationMessage?: string;
    /**
     * A wait message.
     */
    waitMessage?: string;
    /**
     * A success message.
     */
    successMessage?: string;
    /**
     * Is hidden.
     */
    hidden?: boolean;
    /**
     * An icon class.
     *
     * @since 10.0.0
     */
    iconClass?: string;
}

/**
 * A row action.
 */
export interface RowAction {
    /**
     * An action.
     */
    action: string;
    /**
     * A label.
     */
    label?: string;
    /**
     * A link.
     */
    link?: string;
    /**
     * A text.
     */
    text?: string;
    /**
     * Data attributes.
     */
    data?: Record<string, string | number | boolean>;
    /**
     * A group index.
     */
    groupIndex?: number;
}

/**
 * A dropdown item. Handled by a class method `action{Name}` or a handler.
 */
export interface DropdownItem {
    /**
     * A name.
     */
    name: string;
    /**
     * A label. To be translated in a current scope.
     */
    label?: string;
    /**
     * An HTML.
     */
    html?: string;
    /**
     * Hidden.
     */
    hidden?: boolean;
    /**
     * A click handler.
     */
    onClick?: () => void;
    /**
     * An icon class.
     *
     * @since 10.0.0
     */
    iconClass?: string,
}

/**
 * A button. Handled by a class method `action{Name}` or a handler.
 */
export interface Button {
    /**
     * A name.
     */
    name: string;
    /**
     *  A label. To be translated in a current scope.
     */
    label: string;
    /**
     * A style
     */
    style?: 'default' | 'danger' | 'warning' | 'success';
    /**
     * Hidden.
     */
    hidden?: boolean;
    /**
     * A click handler.
     */
    onClick?: () => void;
}

/**
 * Column definitions.
 */
export interface ColumnDefs {
    /**
     * A name (usually a field name).
     */
    name: string;
    /**
     * An overridden field view name.
     */
    view?: string;
    /**
     * A width in percents.
     */
    width?: number;
    /**
     * A width in pixels.
     */
    widthPx?: number;
    /**
     * To use `listLink` mode (link to the detail view).
     */
    link?: boolean;
    /**
     * An alignment.
     */
    align?: 'left' | 'right';
    /**
     * An overridden field type.
     */
    type?: string;
    /**
     * Overridden field parameters.
     */
    params?: Record<string, unknown>;
    /**
     * Field view options.
     */
    options?: Record<string, unknown>;
    /**
     * A label.
     */
    label?: string;
    /**
     * Not sortable.
     */
    notSortable?: boolean;
    /**
     * Hidden by default.
     */
    hidden?: boolean;
    /**
     * No label.
     */
    noLabel?: boolean;
    /**
     * A custom label.
     */
    customLabel?: string;
}

export interface ListRecordViewOptions extends ListBaseRecordViewOptions<ColumnDefs[]> {}

export interface ListRecordViewSchema extends ListBaseRecordViewSchema<ColumnDefs[]> {
    options: ListRecordViewOptions
}

/**
 * A record-list view. Renders and processes list items, actions.
 */
class ListRecordView<
    S extends ListRecordViewSchema = ListRecordViewSchema
> extends ListBaseRecordView<ColumnDefs[], S> {

    protected setup() {
        super.setup();
    }

    /**
     * @internal
     */
    protected _convertLayout(listLayout: ColumnDefs[], model?: Model): any {
        model = model || this.collection.prepareModel();

        const layout = [];

        if (this.checkboxes) {
            layout.push({
                name: 'r-checkboxField',
                columnName: 'r-checkbox',
                template: 'record/list-checkbox',
            });
        }

        for (const col of listLayout) {
            const type = col.type || model.getFieldType(col.name) || 'base';

            if (!col.name) {
                continue;
            }

            const item = {
                columnName: col.name,
                name: col.name + 'Field',
                view: col.view ||
                    model.getFieldParam(col.name, 'view') ||
                    this.getFieldManager().getViewName(type),
                options: {
                    defs: {
                        name: col.name,
                        params: col.params || {}
                    },
                    mode: 'list',
                },
            } as any;

            if (col.width) {
                item.options.defs.width = col.width;
            }

            if (col.widthPx) {
                item.options.defs.widthPx = col.widthPx;
            }

            if (col.link) {
                item.options.mode = 'listLink';
            }
            if (col.align) {
                item.options.defs.align = col.align;
            }

            if (col.options) {
                for (const optionName in col.options) {
                    if (typeof item.options[optionName] !== 'undefined') {
                        continue;
                    }

                    item.options[optionName] = col.options[optionName];
                }
            }

            if (col.name && this._listSettingsHelper) {
                if (this._listSettingsHelper.isColumnHidden(col.name, col.hidden)) {
                    continue;
                }
            }

            if (!this._listSettingsHelper && col.hidden) {
                continue;
            }

            layout.push(item);
        }

        if (this.rowActionsView && !this.rowActionsDisabled) {
            layout.push(this.getRowActionsDefs());
        }

        return layout;
    }
}

export default ListRecordView;

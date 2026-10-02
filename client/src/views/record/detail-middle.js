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

/** @module views/record/detail-middle */

import View from 'view';

/**
 * A detail-middle record view.
 */
class DetailMiddleRecordView extends View {

    init() {
        this.recordHelper = this.options.recordHelper;
        this.scope = this.model.entityType;
    }

    data() {
        return {
            hiddenPanels: this.recordHelper.getHiddenPanels(),
            hiddenFields: this.recordHelper.getHiddenFields(),
        };
    }

    /**
     * Show a panel.
     *
     * @param {string} name
     */
    showPanel(name) {
        if (this.recordHelper.getPanelStateParam(name, 'hiddenLocked')) {
            return;
        }

        this.showPanelInternal(name);

        this.recordHelper.setPanelStateParam(name, 'hidden', false);
    }

    /**
     * @param {string} name
     */
    showPanelInternal(name) {
        if (this.isRendered()) {
            this.$el.find('.panel[data-name="'+name+'"]').removeClass('hidden');
        }

        const wasShown = !this.recordHelper.getPanelStateParam(name, 'hidden');

        if (
            !wasShown &&
            this.options.panelFieldListMap &&
            this.options.panelFieldListMap[name]
        ) {
            this.options.panelFieldListMap[name].forEach(field => {
                const view = this.getFieldView(field);

                if (!view) {
                    return;
                }

                view.reRender();
            });
        }
    }

    /**
     * Hide a panel.
     *
     * @param {string} name
     */
    hidePanel(name) {
        this.hidePanelInternal(name);

        this.recordHelper.setPanelStateParam(name, 'hidden', true);
    }

    /**
     * @public
     * @param {string} name A name.
     */
    hidePanelInternal(name) {
        if (this.isRendered()) {
            this.$el.find('.panel[data-name="'+name+'"]').addClass('hidden');
        }
    }

    /**
     * Hide a field.
     *
     * @param {string} name A name.
     */
    hideField(name) {
        this.recordHelper.setFieldStateParam(name, 'hidden', true);

        const processHtml = () => {
            const fieldView = this.getFieldView(name);

            if (fieldView) {
                const $field = fieldView.$el;
                const $cell = $field.closest('.cell[data-name="' + name + '"]');
                const $label = $cell.find('label.control-label[data-name="' + name + '"]');

                $field.addClass('hidden');
                $label.addClass('hidden');
                $cell.addClass('hidden-cell');
            }
            else {
                this.$el.find('.cell[data-name="' + name + '"]').addClass('hidden-cell');
                this.$el.find('.field[data-name="' + name + '"]').addClass('hidden');
                this.$el.find('label.control-label[data-name="' + name + '"]').addClass('hidden');
            }
        };

        if (this.isRendered()) {
            processHtml();
        }
        else {
            this.once('after:render', () => {
                processHtml();
            });
        }

        const view = this.getFieldView(name);

        if (view) {
            view.setDisabled();
        }
    }

    /**
     * Show a field.
     *
     * @param {string} name A name.
     */
    showField(name) {
        if (this.recordHelper.getFieldStateParam(name, 'hiddenLocked')) {
            return;
        }

        this.recordHelper.setFieldStateParam(name, 'hidden', false);

        const processHtml = () => {
            const fieldView = this.getFieldView(name);

            if (fieldView) {
                const $field = fieldView.$el;
                const $cell = $field.closest('.cell[data-name="' + name + '"]');
                const $label = $cell.find('label.control-label[data-name="' + name + '"]');

                $field.removeClass('hidden');
                $label.removeClass('hidden');
                $cell.removeClass('hidden-cell');
            }
            else {
                this.$el.find('.cell[data-name="' + name + '"]').removeClass('hidden-cell');
                this.$el.find('.field[data-name="' + name + '"]').removeClass('hidden');
                this.$el.find('label.control-label[data-name="' + name + '"]').removeClass('hidden');
            }
        };

        if (this.isRendered()) {
            processHtml();
        }
        else {
            this.once('after:render', () => {
                processHtml();
            });
        }

        const view = this.getFieldView(name);

        if (view) {
            if (!view.disabledLocked) {
                view.setNotDisabled();
            }
        }
    }

    /**
     * Get field views.
     *
     * @return {Object.<string, module:views/fields/base>}
     */
    getFieldViews() {
        const fieldViews = {};

        for (const viewKey in this.nestedViews) {
            // noinspection JSUnresolvedReference
            const name = this.nestedViews[viewKey].name;

            fieldViews[name] = this.nestedViews[viewKey];
        }

        return fieldViews;
    }

    /**
     * Get a field view.
     *
     * @param {string} name A field name.
     * @return {module:views/fields/base}
     */
    getFieldView(name) {
        return (this.getFieldViews() || {})[name];
    }

    /**
     * For backward compatibility.
     *
     * @todo Remove.
     */
    getView(name) {
        let view = super.getView(name);

        if (!view) {
            view = this.getFieldView(name);
        }

        return view;
    }
}

// noinspection JSUnusedGlobalSymbols
export default DetailMiddleRecordView;

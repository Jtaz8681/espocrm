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

import LayoutBaseView from 'views/admin/layouts/base';

/**
 * @abstract
 */
class LayoutRowsView extends LayoutBaseView {

    template = 'admin/layouts/rows'

    dataAttributeList = null
    dataAttributesDefs = {}
    editable = false

    data() {
        return {
            scope: this.scope,
            type: this.type,
            buttonList: this.buttonList,
            enabledFields: this.enabledFields,
            disabledFields: this.disabledFields,
            layout: this.rowLayout,
            dataAttributeList: this.dataAttributeList,
            dataAttributesDefs: this.dataAttributesDefs,
            editable: this.editable,
        };
    }

    setup() {
        this.itemsData = {};

        super.setup();

        this.events['click a[data-action="editItem"]'] = e => {
            const name = $(e.target).closest('li').data('name');

            this.editRow(name);
        };

        this.on('update-item', (name, attributes) => {
            this.itemsData[name] = Espo.Utils.cloneDeep(attributes);
        });

        Espo.loader.require('res!client/css/misc/layout-manager-rows.css', styleCss => {
            this.$style = $('<style>').html(styleCss).appendTo($('body'));
        });
    }

    onRemove() {
        if (this.$style) {
            this.$style.remove();
        }
    }

    editRow(name) {
        const attributes = Espo.Utils.cloneDeep(this.itemsData[name] || {});
        attributes.name = name;

        this.openEditDialog(attributes)
    }

    afterRender() {
        $('#layout ul.enabled, #layout ul.disabled').sortable({
            cursor: 'grabbing',
            connectWith: '#layout ul.connected',
            update: e => {
                if (!$(e.target).hasClass('disabled')) {
                    this.onDrop(e);
                    this.setIsChanged();
                }
            },
        });

        this.$el.find('.enabled-well').focus();
    }

    onDrop(e) {}

    fetch() {
        const layout = [];

        $("#layout ul.enabled > li").each((i, el) => {
            const o = {};

            const name = $(el).data('name');

            const attributes = this.itemsData[name] || {};
            attributes.name = name;

            this.dataAttributeList.forEach(attribute => {
                const defs = this.dataAttributesDefs[attribute] || {};

                if (defs.notStorable) {
                    return;
                }

                const value = attributes[attribute] || null;

                if (value) {
                    o[attribute] = value;
                }
            });

            layout.push(o);
        });

        return layout;
    }

    /**
     * @protected
     * @param {Object|Array} layout
     * @return {boolean}
     */
    validate(layout) {
        if (layout.length === 0) {
            Espo.Ui.error(this.translate('cantBeEmpty', 'messages', 'LayoutManager'));

            return false;
        }

        return true;
    }
}

export default LayoutRowsView;

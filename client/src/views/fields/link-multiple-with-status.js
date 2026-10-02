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

import LinkMultipleFieldView from 'views/fields/link-multiple';

class LinkMultipleWithStatusFieldView extends LinkMultipleFieldView {

    /**
     * @protected
     * @type {string[]}
     */
    canceledStatusList

    setup() {
        super.setup();

        this.columnsName = this.name + 'Columns';
        this.columns = Espo.Utils.cloneDeep(this.model.get(this.columnsName) || {});

        this.listenTo(this.model, 'change:' + this.columnsName, () => {
            this.columns = Espo.Utils.cloneDeep(this.model.get(this.columnsName) || {});
        });

        this.statusField = this.getMetadata()
            .get(['entityDefs', this.model.entityType,  'fields', this.name, 'columns', 'status']);

        this.styleMap = this.getMetadata()
            .get(['entityDefs', this.foreignScope, 'fields', this.statusField, 'style']) || {};

        this.canceledStatusList = [];
    }

    getAttributeList() {
        const list = super.getAttributeList();

        list.push(this.name + 'Columns');

        return list;
    }

    getDetailLinkHtml(id, name) {
        const status = (this.columns[id] || {}).status;

        if (!status) {
            return super.getDetailLinkHtml(id, name);
        }

        const style = this.styleMap[status];

        const targetStyleList = ['success', 'info', 'danger', 'warning'];

        if (!style || !~targetStyleList.indexOf(style)) {
            return super.getDetailLinkHtml(id, name);
        }

        let iconStyle = '';

        if (style === 'success') {
            iconStyle = 'fas fa-check text-success small';
        } else if (this.canceledStatusList.includes(status)) {
            iconStyle = `fas fa-times text-${style} small`;
        }

        return `<span class="${iconStyle}" style="display: inline-block; min-width: 12px"></span> ` +
            super.getDetailLinkHtml(id, name);
    }
}

// noinspection JSUnusedGlobalSymbols
export default LinkMultipleWithStatusFieldView;

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

import LayoutIndexView from 'views/admin/layouts/index';

class LayoutsView extends LayoutIndexView {

    setup() {
        const setId = this.setId = this.options.layoutSetId;

        this.baseUrl = `#LayoutSet/editLayouts/id=${setId}`;

        super.setup();

        this.wait(
            this.getModelFactory()
                .create('LayoutSet')
                .then(m => {
                    this.sModel = m;
                    m.id = setId;

                    return m.fetch();
                })
        );
    }

    getLayoutScopeDataList() {
        const dataList = [];
        const list = this.sModel.get('layoutList') || [];

        const scopeList = [];

        list.forEach(item => {
            const arr = item.split('.');
            const scope = arr[0];

            if (scopeList.includes(scope)) {
                return;
            }

            scopeList.push(scope);
        });

        scopeList.forEach(scope => {
            const o = {};

            o.scope = scope;
            o.url = this.baseUrl + '&scope=' + scope;
            o.typeDataList = [];

            const typeList = [];

            list.forEach(item => {
                const [scope, type] = item.split('.');

                if (scope !== o.scope) {
                    return;
                }

                typeList.push(type);
            });

            typeList.forEach(type => {
                o.typeDataList.push({
                    type: type,
                    url: `${this.baseUrl}&scope=${scope}&type=${type}`,
                    label: this.translateLayoutName(type, scope),
                });
            });

            o.typeList = typeList;

            dataList.push(o);
        });

        return dataList;
    }

    getHeaderHtml() {
        const separatorHtml = ' <span class="breadcrumb-separator"><span></span></span> ';

        return $('<span>')
            .append(
                $('<a>')
                    .attr('href', '#LayoutSet')
                    .text(this.translate('LayoutSet', 'scopeNamesPlural')),
                separatorHtml,
                $('<a>')
                    .attr('href', '#LayoutSet/view/' + this.sModel.id)
                    .text(this.sModel.get('name')),
                separatorHtml,
                $('<span>')
                    .text(this.translate('Edit Layouts', 'labels', 'LayoutSet'))
            )
            .get(0).outerHTML;
    }

    navigate(scope, type) {
        const url = '#LayoutSet/editLayouts/id=' + this.setId + '&scope=' + scope + '&type=' + type;

        this.getRouter().navigate(url, {trigger: false});
    }
}

export default LayoutsView;

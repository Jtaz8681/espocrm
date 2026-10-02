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

import MultiEnumFieldView from 'views/fields/multi-enum';
import LayoutsIndex from 'views/admin/layouts/index';

export default class extends MultiEnumFieldView {

    typeList = [
        'list',
        'detail',
        'listSmall',
        'detailSmall',
        'bottomPanelsDetail',
        'filters',
        'massUpdate',
        'sidePanelsDetail',
        'sidePanelsEdit',
        'sidePanelsDetailSmall',
        'sidePanelsEditSmall',
        'defaultSidePanel',
    ]

    setupOptions() {
        this.params.options = [];
        this.translatedOptions = {};

        this.scopeList = Object.keys(this.getMetadata().get('scopes'))
            .filter(item => this.getMetadata().get(['scopes', item, 'layouts']))
            .sort((v1, v2) => {
                return this.translate(v1, 'scopeNames')
                    .localeCompare(this.translate(v2, 'scopeNames'));
            });

        const dataList = LayoutsIndex.prototype.getLayoutScopeDataList.call(this);

        dataList.forEach(item1 => {
            item1.typeList.forEach(type => {
                const item = item1.scope + '.' + type;

                if (type.substr(-6) === 'Portal') {
                    return;
                }

                this.params.options.push(item);

                this.translatedOptions[item] = this.translate(item1.scope, 'scopeNames') + ' . ' +
                    this.translate(type, 'layouts', 'Admin');
            });
        });
    }

    // noinspection JSUnusedGlobalSymbols
    translateLayoutName(type, scope) {
        return LayoutsIndex.prototype.translateLayoutName.call(this, type, scope);
    }
}

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

export default class extends ModalView {

    cssName = 'user-access'
    multiple = false
    template = 'user/modals/access'
    backdrop = true

    styleMap = {
        yes: 'success',
        all: 'success',
        account: 'info',
        contact: 'info',
        team: 'info',
        own: 'warning',
        no: 'danger',
        enabled: 'success',
        disabled: 'danger',
        'not-set': 'default',
    }

    data() {
        return {
            valuePermissionDataList: this.getValuePermissionList(),
            levelListTranslation: this.getLanguage().get('Role', 'options', 'levelList') || {},
            styleMap: this.styleMap,
        };
    }

    getValuePermissionList() {
        const list = this.getMetadata().get(['app', 'acl', 'valuePermissionList'], []);
        const dataList = [];

        list.forEach(item => {
            const o = {};
            o.name = item;
            o.value = this.options.aclData[item];

            dataList.push(o);
        });

        return dataList;
    }

    setup() {
        this.buttonList = [
            {
                name: 'cancel',
                label: 'Cancel',
            }
        ];

        const fieldTable = Espo.Utils.cloneDeep(this.options.aclData.fieldTable || {});

        for (const scope in fieldTable) {
            const scopeData = fieldTable[scope] || {};

            for (const field in scopeData) {
                if (
                    this.getMetadata()
                        .get(['app', 'acl', 'mandatory', 'scopeFieldLevel', scope, field]) !== null
                ) {
                    delete scopeData[field];
                }

                if (
                    scopeData[field] &&
                    this.getMetadata().get(['entityDefs', scope, 'fields', field, 'readOnly'])
                ) {
                    if (scopeData[field].edit === 'no' && scopeData[field].read === 'yes') {
                        delete scopeData[field];
                    }
                }
            }
        }

        this.createView('table', 'views/role/record/table', {
            acl: {
                data: this.options.aclData.table,
                fieldData: fieldTable,
            },
            final: true,
            selector: '.user-access-table',
        });

        this.headerText = this.translate('Access');
    }
}

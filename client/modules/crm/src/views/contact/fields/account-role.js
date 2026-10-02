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

define('crm:views/contact/fields/account-role', ['views/fields/varchar'], function (Dep) {

    return Dep.extend({

        detailTemplate: 'crm:contact/fields/account-role/detail',
        listTemplate: 'crm:contact/fields/account-role/detail',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'change:title', () => {
                this.model.set('accountRole', this.model.get('title'));
            });
        },

        getAttributeList: function () {
            var list = Dep.prototype.getAttributeList.call(this);

            list.push('title');
            list.push('accountIsInactive');

            return list;
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

            if (this.model.has('accountIsInactive')) {
                data.accountIsInactive = this.model.get('accountIsInactive');
            }

            return data;
        }
    });
});

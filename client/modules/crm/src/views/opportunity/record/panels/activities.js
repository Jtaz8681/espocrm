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

define('crm:views/opportunity/record/panels/activities', ['crm:views/record/panels/activities'], function (Dep) {

    return Dep.extend({

        getComposeEmailAttributes: function (scope, data, callback) {
            data = data || {};

            Espo.Ui.notifyWait();

            Dep.prototype.getComposeEmailAttributes.call(this, scope, data, (attributes) => {
                Espo.Ajax.getRequest('Opportunity/action/emailAddressList?id=' + this.model.id).then(list => {
                    attributes.to = '';
                    attributes.cc = '';
                    attributes.nameHash = {};

                    list.forEach(item => {
                        attributes.to += item.emailAddress + ';';
                        attributes.nameHash[item.emailAddress] = item.name;
                    });

                    Espo.Ui.notify(false);

                    callback.call(this, attributes);

                });
            })
        },
    });
});

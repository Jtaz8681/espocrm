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

define('crm:views/campaign/modals/mail-merge-pdf', ['views/modal', 'ui/select'],
function (Dep, /** module:ui/select */ Select) {

    return Dep.extend({

        template: 'crm:campaign/modals/mail-merge-pdf',

        data: function () {
            return {
                linkList: this.linkList,
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.headerText = this.translate('Generate Mail Merge PDF', 'labels', 'Campaign');

            var linkList = ['contacts', 'leads', 'accounts', 'users'];
            this.linkList = [];

            linkList.forEach(link => {
                if (!this.model.get(link + 'TemplateId')) {
                    return;
                }

                let targetEntityType = this.getMetadata()
                    .get(['entityDefs', 'TargetList', 'links', link, 'entity']);

                if (!this.getAcl().checkScope(targetEntityType)) {
                    return;
                }

                this.linkList.push(link);
            });

            this.buttonList.push({
                name: 'proceed',
                label: 'Proceed',
                style: 'danger'
            });

            this.buttonList.push({
                name: 'cancel',
                label: 'Cancel'
            });
        },

        afterRender: function () {
            Select.init(this.$el.find('.field[data-name="link"] select'));
        },

        actionProceed: function () {
            let link = this.$el.find('.field[data-name="link"] select').val();

            this.trigger('proceed', link);
        },
    });
});

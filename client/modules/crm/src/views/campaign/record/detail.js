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

define('crm:views/campaign/record/detail', ['views/record/detail'], function (Dep) {

    return Dep.extend({

        duplicateAction: true,

        setupActionItems: function () {
            Dep.prototype.setupActionItems.call(this);
            this.dropdownItemList.push({
                'label': 'Generate Mail Merge PDF',
                'name': 'generateMailMergePdf',
                'hidden': !this.isMailMergeAvailable()
            });

            this.listenTo(this.model, 'change', function () {
                if (this.isMailMergeAvailable()) {
                    this.showActionItem('generateMailMergePdf');
                } else {
                    this.hideActionItem('generateMailMergePdf');
                }
            }, this);
        },

        afterRender: function () {
        	Dep.prototype.afterRender.call(this);
        },

        isMailMergeAvailable: function () {
            if (this.model.get('type') !== 'Mail') {
                return false;
            }

            if (!this.model.get('targetListsIds') || !this.model.get('targetListsIds').length) {
                return false;
            }

            if (
                !this.model.get('leadsTemplateId') &&
                !this.model.get('contactsTemplateId') &&
                !this.model.get('accountsTemplateId') &&
                !this.model.get('usersTemplateId')
            ) {
                return false;
            }

            return true;
        },

        actionGenerateMailMergePdf: function () {
            this.createView('dialog', 'crm:views/campaign/modals/mail-merge-pdf', {
                model: this.model,
            }, function (view) {
                view.render();

                this.listenToOnce(view, 'proceed', (link) => {
                    this.clearView('dialog');

                    Espo.Ui.notifyWait();

                    Espo.Ajax.postRequest(`Campaign/${this.model.id}/generateMailMerge`, {link: link})
                        .then(response => {
                            Espo.Ui.notify(false);

                            window.open('?entryPoint=download&id=' + response.id, '_blank');
                        });
                });
            });
        },
    });
});

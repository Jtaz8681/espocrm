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

import View from 'view';

export default class UpgradeIndexView extends View {

    template = 'admin/upgrade/index'

    packageContents = null

    data() {
        return {
            versionMsg: this.translate('Current version') + ': ' + this.getConfig().get('version'),
            infoMsg: this.translate('upgradeInfo', 'messages', 'Admin')
                .replace('{url}', '#'),
            backupsMsg: this.translate('upgradeBackup', 'messages', 'Admin'),
            upgradeRecommendation: this.translate('upgradeRecommendation', 'messages', 'Admin'),
            downloadMsg: this.translate('downloadUpgradePackage', 'messages', 'Admin')
                .replace('{url}', '#'),
        };
    }

    afterRender() {
        this.$el.find('.panel-body a').attr('target', '_BLANK');
    }

    events = {
        /** @this UpgradeIndexView */
        'change input[name="package"]': function (e) {
            this.$el.find('button[data-action="upload"]')
                .addClass('disabled')
                .attr('disabled', 'disabled');

            this.$el.find('.message-container').html('');

            const files = e.currentTarget.files;

            if (files.length) {
                this.selectFile(files[0]);
            }
        },
        /** @this UpgradeIndexView */
        'click button[data-action="upload"]': function () {
            this.upload();
        },
    }

    selectFile(file) {
        const fileReader = new FileReader();

        fileReader.onload = (e) => {
            this.packageContents = e.target.result;

            this.$el.find('button[data-action="upload"]')
                .removeClass('disabled')
                .removeAttr('disabled');
        };

        fileReader.readAsDataURL(file);
    }

    showError(msg) {
        msg = this.translate(msg, 'errors', 'Admin');

        this.$el.find('.message-container').html(msg);
    }

    upload() {
        this.$el.find('button[data-action="upload"]')
            .addClass('disabled')
            .attr('disabled', 'disabled');

        Espo.Ui.notify(this.translate('Uploading...'));

        Espo.Ajax
            .postRequest('Admin/action/uploadUpgradePackage', this.packageContents, {
                contentType: 'application/zip',
                timeout: 0,
            })
            .then(data => {
                if (!data.id) {
                    this.showError(this.translate('Error occurred'));

                    return;
                }

                Espo.Ui.notify(false);

                this.createView('popup', 'views/admin/upgrade/ready', {
                    upgradeData: data,
                }, view => {
                    view.render();

                    this.$el.find('button[data-action="upload"]')
                        .removeClass('disabled')
                        .removeAttr('disabled');

                    view.once('run', () => {
                        view.close();

                        this.$el.find('.panel.upload').addClass('hidden');

                        this.run(data.id, data.version);
                    });
                });
            })
            .catch(xhr => {
                this.showError(xhr.getResponseHeader('X-Status-Reason'));

                Espo.Ui.notify(false);
            });
    }

    textNotification(text) {
        this.$el.find('.notify-text').html(text);
    }

    run(id, version) {
        const msg = this.translate('Upgrading...', 'labels', 'Admin');

        Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

        this.textNotification(msg);

        Espo.Ajax
            .postRequest('Admin/action/runUpgrade', {id: id}, {timeout: 0, bypassAppReload: true})
            .then(() => {
                const cache = this.getCache();

                if (cache) {
                    cache.clear();
                }

                this.createView('popup', 'views/admin/upgrade/done', {
                    version: version,
                }, view => {
                    Espo.Ui.notify(false);

                    view.render();
                });
            })
            .catch(xhr => {
                this.$el.find('.panel.upload').removeClass('hidden');

                const msg = xhr.getResponseHeader('X-Status-Reason');

                this.textNotification(this.translate('Error') + ': ' + msg);
            });
    }
}

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
import SelectProvider from 'helpers/list/select-provider';

class IndexExtensionsView extends View {

    template = 'admin/extensions/index'

    packageContents = null

    events = {
        /** @this IndexExtensionsView */
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
        /** @this IndexExtensionsView */
        'click button[data-action="upload"]': function () {
            this.upload();
        },
        /** @this IndexExtensionsView */
        'click [data-action="install"]': function (e) {
            const id = $(e.currentTarget).data('id');

            const name = this.collection.get(id).get('name');
            const version = this.collection.get(id).get('version');

            this.run(id, name, version);

        },
        /** @this IndexExtensionsView */
        'click [data-action="uninstall"]': function (e) {
            const id = $(e.currentTarget).data('id');

            this.confirm(this.translate('uninstallConfirmation', 'messages', 'Admin'), () => {
                Espo.Ui.notify(this.translate('Uninstalling...', 'labels', 'Admin'));

                Espo.Ajax
                    .postRequest('Extension/action/uninstall', {id: id}, {timeout: 0, bypassAppReload: true})
                    .then(() => {
                        Espo.Ui.success(this.translate('Done'));

                        setTimeout(() => window.location.reload(), 500);
                    })
                    .catch(xhr => {
                        const msg = xhr.getResponseHeader('X-Status-Reason');

                        this.showErrorNotification(this.translate('Error') + ': ' + msg);
                    });
            });
        }
    }

    setup() {
        const selectProvider = new SelectProvider();

        this.wait(
            this.getCollectionFactory()
                .create('Extension')
                .then(collection => {
                    this.collection = collection;
                    this.collection.maxSize = this.getConfig().get('recordsPerPage');
                })
                .then(() => selectProvider.get('Extension'))
                .then(select => {
                    this.collection.data.select = select.join(',');
                })
                .then(() => this.collection.fetch())
                .then(() => {
                    this.createView('list', 'views/extension/record/list', {
                        collection: this.collection,
                        selector: '> .list-container',
                    });

                    if (this.collection.length === 0) {
                        this.once('after:render', () => {
                            this.$el.find('.list-container').addClass('hidden');
                        });
                    }
                })
        );
    }

    selectFile(file) {
        const fileReader = new FileReader();

        fileReader.onload = (e) => {
            this.packageContents = e.target.result;

            this.$el.find('button[data-action="upload"]')
                .removeClass('disabled')
                .removeAttr('disabled');

            const maxSize = this.getHelper().getAppParam('maxUploadSize') || 0;

            if (file.size > maxSize * 1024 * 1024) {
                const body = this.translate('fileExceedsMaxUploadSize', 'messages', 'Extension')
                    .replace('{maxSize}', maxSize + 'MB');

                Espo.Ui.dialog({
                    body: this.getHelper().transformMarkdownText(body).toString(),
                    buttonList: [
                        {
                            name: 'close',
                            text: this.translate('Close'),
                            onClick: dialog => dialog.close(),
                        },
                    ],
                }).show();
            }
        };

        fileReader.readAsDataURL(file);
    }

    showError(msg) {
        msg = this.translate(msg, 'errors', 'Admin');

        this.$el.find('.message-container').html(msg);
    }

    showErrorNotification(msg) {
        if (!msg) {
            this.$el.find('.notify-text').addClass('hidden');

            return;
        }

        msg = this.translate(msg, 'errors', 'Admin');

        this.$el.find('.notify-text').html(msg);
        this.$el.find('.notify-text').removeClass('hidden');
    }

    upload() {
        this.$el.find('button[data-action="upload"]').addClass('disabled').attr('disabled', 'disabled');

        Espo.Ui.notify(this.translate('Uploading...'));

        Espo.Ajax
            .postRequest('Extension/action/upload', this.packageContents, {
                timeout: 0,
                contentType: 'application/zip',
            })
            .then(data => {
                if (!data.id) {
                    this.showError(this.translate('Error occurred'));

                    return;
                }

                Espo.Ui.notify(false);

                this.createView('popup', 'views/admin/extensions/ready', {
                    upgradeData: data,
                }, view => {
                    view.render();

                    this.$el.find('button[data-action="upload"]')
                        .removeClass('disabled')
                        .removeAttr('disabled');

                    view.once('run', () => {
                        view.close();

                        this.$el.find('.panel.upload').addClass('hidden');

                        this.run(data.id, data.version, data.name);
                    });
                });
            })
            .catch(xhr => {
                this.showError(xhr.getResponseHeader('X-Status-Reason'));

                Espo.Ui.notify(false);
            });
    }

    run(id, version, name) {
        Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

        this.showError(false);
        this.showErrorNotification(false);

        Espo.Ajax
            .postRequest('Extension/action/install', {id: id}, {timeout: 0, bypassAppReload: true})
            .then(() => {
                const cache = this.getCache();

                if (cache) {
                    cache.clear();
                }

                this.createView('popup', 'views/admin/extensions/done', {
                    version: version,
                    name: name,
                }, view => {
                    if (this.collection.length) {
                        this.collection.fetch({bypassAppReload: true});
                    }

                    this.$el.find('.list-container').removeClass('hidden');
                    this.$el.find('.panel.upload').removeClass('hidden');

                    Espo.Ui.notify(false);

                    view.render();
                });
            })
            .catch(xhr => {
                this.$el.find('.panel.upload').removeClass('hidden');

                const msg = xhr.getResponseHeader('X-Status-Reason');

                this.showErrorNotification(this.translate('Error') + ': ' + msg);
            });
    }
}

export default IndexExtensionsView;

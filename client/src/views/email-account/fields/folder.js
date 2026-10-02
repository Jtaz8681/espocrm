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

import BaseFieldView from 'views/fields/base';

export default class extends BaseFieldView {

    editTemplate = 'email-account/fields/folder/edit'

    getFoldersUrl = 'EmailAccount/action/getFolders'

    setup() {
        super.setup();

        this.addActionHandler('selectFolder', () => {
            Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

            const data = {
                host: this.model.get('host'),
                port: this.model.get('port'),
                security: this.model.get('security'),
                username: this.model.get('username'),
                emailAddress: this.model.get('emailAddress'),
                userId: this.model.get('assignedUserId'),
            };

            if (this.model.has('password')) {
                data.password = this.model.get('password');
            }

            if (!this.model.isNew()) {
                data.id = this.model.id;
            }

            Espo.Ajax
                .postRequest(this.getFoldersUrl, data).then(folders => {
                    this.createView('modal', 'views/email-account/modals/select-folder', {
                        folders: folders
                    }, view => {
                        Espo.Ui.notify(false);

                        view.render();

                        this.listenToOnce(view, 'select', (folder) => {
                            view.close();

                            this.addFolder(folder);
                        });
                    });
                })
                .catch(xhr => {
                    Espo.Ui.error(this.translate('couldNotConnectToImap', 'messages', 'EmailAccount'));

                    xhr.errorIsHandled = true;
                });
        })
    }

    addFolder(folder) {
        this.$element.val(folder);
    }
}

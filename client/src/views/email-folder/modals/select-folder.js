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

    template = 'email-folder/modals/select-folder'

    cssName = 'select-folder'
    backdrop = true

    /** @const */
    FOLDER_ALL = 'all'
    /** @const */
    FOLDER_INBOX = 'inbox'
    /** @const */
    FOLDER_IMPORTANT = 'important'
    /** @const */
    FOLDER_SENT = 'sent'
    /** @const */
    FOLDER_DRAFTS = 'drafts'
    /** @const */
    FOLDER_TRASH = 'trash'
    /** @const */
    FOLDER_ARCHIVE = 'archive'

    data() {
        return {
            folderDataList: this.folderDataList,
        };
    }

    /**
     * @private
     * @type {string|undefined}
     */
    currentFolderId

    setup() {
        this.addActionHandler('selectFolder', (e, target) => {
            const id = target.dataset.id;
            const name = target.dataset.name;

            this.trigger('select', id, name);
            this.close();
        });

        this.headerText = this.options.headerText || '';
        this.isGroup = this.options.isGroup || false;
        this.noArchive = this.options.noArchive || false;
        this.currentFolderId = this.options.currentFolderId;

        if (this.headerText === '') {
            this.buttonList.push({
                name: 'cancel',
                label: 'Cancel',
            });
        }

        Espo.Ui.notifyWait();

        this.wait(
            Espo.Ajax.getRequest('EmailFolder/action/listAll')
                .then(/** {list: {id: string, name: string}[]} */data => {
                    Espo.Ui.notify(false);

                    const builtInFolders = [
                        this.FOLDER_INBOX,
                        this.FOLDER_IMPORTANT,
                        this.FOLDER_SENT,
                        this.FOLDER_DRAFTS,
                        this.FOLDER_TRASH,
                        this.FOLDER_ARCHIVE,
                    ];

                    const iconMap = {
                        [this.FOLDER_ALL]: 'far fa-hdd',
                        [this.FOLDER_TRASH]: 'far fa-trash-alt',
                        [this.FOLDER_SENT]: 'far fa-paper-plane',
                        [this.FOLDER_INBOX]: 'fas fa-inbox',
                        [this.FOLDER_ARCHIVE]: 'far fa-caret-square-down',
                    };

                    this.folderDataList = data.list
                        .filter(item => {
                            if (this.isGroup && !item.id.startsWith('group:')) {
                                return false;
                            }

                            return !builtInFolders.includes(item.id);
                        })
                        .map(item => {
                            const isGroup = item.id.startsWith('group:');

                            return {
                                disabled: item.id === this.currentFolderId,
                                id: item.id,
                                name: item.name,
                                isGroup: isGroup,
                                iconClass: isGroup ? 'far fa-circle' : 'far fa-folder',
                            };
                        });

                    this.folderDataList.unshift({
                        id: 'inbox',
                        name: this.isGroup ?
                            this.translate('all', 'presetFilters', 'Email') :
                            this.translate('inbox', 'presetFilters', 'Email'),
                        iconClass: this.isGroup ?
                            iconMap[this.FOLDER_ALL] :
                            iconMap[this.FOLDER_INBOX],
                    });

                    if (!this.noArchive) {
                        this.folderDataList.push({
                            id: this.FOLDER_ARCHIVE,
                            name: this.translate('archive', 'presetFilters', 'Email'),
                            iconClass: iconMap[this.FOLDER_ARCHIVE],
                            disabled: this.currentFolderId === this.FOLDER_ARCHIVE,
                        });
                    }
                })
        );
    }
}

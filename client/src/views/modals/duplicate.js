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

class DuplicateModalView extends ModalView {

    template = 'modals/duplicate'

    cssName = 'duplicate-modal'

    data() {
        return {
            scope: this.scope,
            duplicates: this.duplicates,
        };
    }

    setup() {
        let saveLabel = 'Save';

        if (this.model && this.model.isNew()) {
            saveLabel = 'Create';
        }

        this.buttonList = [
            {
                name: 'save',
                label: saveLabel,
                style: 'danger',
                onClick: dialog => {
                    this.trigger('save');

                    dialog.close();
                },
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.scope = this.options.scope;
        this.duplicates = this.options.duplicates;

        if (this.scope) {
            this.setupRecord();
        }
    }

    setupRecord() {
        let promise = new Promise(resolve => {
            this.getHelper().layoutManager.get(this.scope, 'listSmall', layout => {
                layout = Espo.Utils.cloneDeep(layout);
                layout.forEach(item => item.notSortable = true);

                this.getCollectionFactory().create(this.scope)
                    .then(collection => {
                        collection.add(this.duplicates);

                        this.createView('record', 'views/record/list', {
                            selector: '.list-container',
                            collection: collection,
                            listLayout: layout,
                            buttonsDisabled: true,
                            massActionsDisabled: true,
                            rowActionsDisabled: true,
                        });

                        resolve();
                    });
            });
        })

        this.wait(promise);
    }
}

export default DuplicateModalView;

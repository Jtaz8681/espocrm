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

class CreatePostModalView extends ModalView {

    templateContent = `
        <div class="record no-side-margin">{{{record}}}</div>
    `

    setup() {
        this.headerText = this.translate('Create Post');

        this.buttonList = [
            {
                name: 'post',
                label: 'Post',
                style: 'primary',
                title: 'Ctrl+Enter',
                onClick: () => this.post(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
                title: 'Esc',
                onClick: dialog => {
                    dialog.close();
                },
            }
        ];

        this.wait(true);

        this.getModelFactory().create('Note', model => {
            this.createView('record', 'views/stream/record/edit', {
                model: model,
                selector: '.record',
            }, view => {
                this.listenTo(view, 'after:save', () => {
                    this.trigger('after:save');
                });

                this.listenTo(view, 'disable-post-button', () => this.disableButton('post'));
                this.listenTo(view, 'enable-post-button', () => this.enableButton('post'));
            });

            this.wait(false);
        });

        this.shortcutKeys = {
            'Control+Enter': e => {
                e.preventDefault();
                e.stopPropagation();

                this.post();
            }
        };
    }

    /**
     * @return {import('views/record/edit').default}
     */
    getRecordView() {
        return /** @type {import('views/record/edit').default} */this.getView('record');
    }

    post() {
        this.getRecordView().save();
    }
}

export default CreatePostModalView;

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

export default class extends View {

    template = 'email-folder/list-side'

    FOLDER_ALL = 'all'
    FOLDER_INBOX = 'inbox'
    FOLDER_DRAFTS = 'drafts'

    data() {
        const data = {};

        data.selectedFolderId = this.selectedFolderId;
        data.showEditLink = this.options.showEditLink;
        data.scope = this.scope;

        return data;
    }

    actionSelectFolder(id) {
        this.$el.find('li.selected').removeClass('selected');

        this.selectFolder(id);

        this.$el.find(`li[data-id="${id}"]`).addClass('selected');
    }

    setup() {
        this.addActionHandler('selectFolder', (e, target) => {
            e.preventDefault();

            this.actionSelectFolder(target.dataset.id);
        });

        this.scope = 'EmailFolder';
        this.selectedFolderId = this.options.selectedFolderId || this.FOLDER_ALL;
        this.emailCollection = this.options.emailCollection;

        this.loadNotReadCounts();

        this.listenTo(this.emailCollection, 'sync', this.loadNotReadCounts);
        this.listenTo(this.emailCollection, 'folders-update', this.loadNotReadCounts);

        this.listenTo(this.emailCollection, 'all-marked-read', () => {
            this.countsData = this.countsData || {};

            for (const id in this.countsData) {
                if (id === this.FOLDER_DRAFTS) {
                    continue;
                }

                this.countsData[id] = 0;
            }

            this.renderCounts();
            this.loadNotReadCounts();
        });

        this.listenTo(this.emailCollection, 'draft-sent', () => {
            this.decreaseNotReadCount(this.FOLDER_DRAFTS);
            this.renderCounts();
        });

        this.listenTo(this.emailCollection, 'change:isRead', model => {
            if (this.countsIsBeingLoaded) {
                return;
            }

            this.manageCountsDataAfterModelChanged(model);
        });

        this.listenTo(this.emailCollection, 'model-removing', id => {
            const model = this.emailCollection.get(id);

            if (!model) {
                return;
            }

            if (this.countsIsBeingLoaded) {
                return;
            }

            this.manageModelRemoving(model);
        });

        this.listenTo(this.emailCollection, 'moving-to-trash', (id, model) => {
            model = this.emailCollection.get(id) || model;

            if (!model) {
                return;
            }

            if (this.countsIsBeingLoaded) {
                return;
            }

            this.manageModelRemoving(model);
        });

        this.listenTo(this.emailCollection, 'retrieving-from-trash', (id, model) => {
            model = this.emailCollection.get(id) || model;

            if (!model) {
                return;
            }

            if (this.countsIsBeingLoaded) {
                return;
            }

            this.manageModelRetrieving(model);
        });
    }

    manageModelRemoving(model) {
        if (model.get('status') === 'Draft') {
            this.decreaseNotReadCount(this.FOLDER_DRAFTS);
            this.renderCounts();

            return;
        }

        if (!model.get('isUsers')) {
            return;
        }

        if (model.get('isRead')) {
            return;
        }

        let folderId = model.get('groupFolderId') ?
            ('group:' + model.get('groupFolderId')) :
            (model.get('folderId') || this.FOLDER_INBOX);

        this.decreaseNotReadCount(folderId);
        this.renderCounts();
    }

    manageModelRetrieving(model) {
        if (!model.get('isUsers')) {
            return;
        }

        if (model.get('isRead')) {
            return;
        }

        const folderId = model.get('groupFolderId') ?
            ('group:' + model.get('groupFolderId')) :
            (model.get('folderId') || this.FOLDER_INBOX);

        this.increaseNotReadCount(folderId);
        this.renderCounts();
    }

    manageCountsDataAfterModelChanged(model) {
        if (!model.get('isUsers')) {
            return;
        }

        const folderId = model.get('groupFolderId') ?
            ('group:' + model.get('groupFolderId')) :
            (model.get('folderId') || this.FOLDER_INBOX);

        !model.get('isRead') ?
            this.increaseNotReadCount(folderId) :
            this.decreaseNotReadCount(folderId);

        this.renderCounts();
    }

    increaseNotReadCount(folderId) {
        this.countsData = this.countsData || {};
        this.countsData[folderId] = this.countsData[folderId] || 0;
        this.countsData[folderId]++;
    }

    decreaseNotReadCount(folderId) {
        this.countsData = this.countsData || {};

        this.countsData[folderId] = this.countsData[folderId] || 0;

        if (this.countsData[folderId]) {
            this.countsData[folderId]--;
        }
    }

    selectFolder(id) {
        this.emailCollection.reset();
        this.emailCollection.abortLastFetch();

        this.selectedFolderId = id;
        this.trigger('select', id);
    }

    afterRender() {
        if (this.countsData) {
            this.renderCounts();
        }
    }

    loadNotReadCounts() {
        if (this.countsIsBeingLoaded) {
            return;
        }

        this.countsIsBeingLoaded = true;

        Espo.Ajax.getRequest('Email/inbox/notReadCounts').then(data => {
            this.countsData = data;

            if (this.isRendered()) {
                this.renderCounts();
                this.countsIsBeingLoaded = false;

                return;
            }

            this.once('after:render', () => {
                this.renderCounts();
                this.countsIsBeingLoaded = false;
            });
        });
    }

    renderCounts() {
        const data = this.countsData;

        for (const id in data) {
            let value = '';

            if (data[id]) {
                value = data[id].toString();
            }

            this.$el.find(`li a.count[data-id="${id}"]`).text(value);
        }
    }
}

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

import MainView from 'views/main';
import DebounceHelper from 'helpers/util/debounce';
import {inject} from 'di';
import WebSocketManager from 'web-socket-manager';

export default class NoteDetailView extends MainView {

    templateContent = `
        <div class="header page-header">{{{header}}}</div>
        <div class="record list-container list-container-panel block-center">{{{record}}}</div>
    `

    /**
     * @private
     */
    isDeleted = false

    /**
     * @private
     * @type {DebounceHelper}
     */
    webSocketDebounceHelper

    /**
     * @private
     * @type {WebSocketManager}
     */
    @inject(WebSocketManager)
    webSocketManager

    setup() {
        this.scope = this.model.entityType;

        this.setupHeader();
        this.setupRecord();
        this.setupWebSocket();

        this.listenToOnce(this.model, 'remove', () => {
            this.clearView('record');
            this.isDeleted = true;
            this.getHeaderView().reRender();
        });

        this.addActionHandler('fullRefresh', () => this.actionFullRefresh());
    }

    /**
     * @private
     */
    setupHeader() {
        this.createView('header', 'views/header', {
            selector: '> .header',
            scope: this.scope,
            fontSizeFlexible: true,
        });
    }

    /**
     * @private
     */
    setupRecord() {
        this.wait(
            (async () => {
                this.collection = await this.getCollectionFactory().create(this.scope);
                this.collection.add(this.model);

                const view = await this.createView('record', 'views/stream/record/list', {
                    selector: '> .record',
                    collection: this.collection,
                    isUserStream: true,
                });

                if (this.webSocketDebounceHelper) {
                    this.listenTo(view, 'before:save', () => this.webSocketDebounceHelper.block());
                }
            })()
        );
    }

    getHeader() {
        const parentType = this.model.attributes.parentType;
        const parentId = this.model.attributes.parentId;

        const typeText = document.createElement('span');
        typeText.textContent = this.getLanguage().translateOption(this.model.attributes.type, 'type', 'Note');

        if (this.model.attributes.deleted || this.isDeleted) {
            typeText.style.textDecoration = 'line-through';
        }

        typeText.title = this.translate('clickToRefresh', 'messages');
        typeText.dataset.action = 'fullRefresh';
        typeText.style.cursor = 'pointer';

        if (parentType && parentId) {
            return this.buildHeaderHtml([
                (() => {
                    const a = document.createElement('a');
                    a.href = `#${parentType}`;
                    a.textContent = this.translate(parentType, 'scopeNamesPlural');

                    return a;
                })(),
                (() => {
                    const a = document.createElement('a');
                    a.href = `#${parentType}/view/${parentId}`;
                    a.textContent = this.model.attributes.parentName || parentId;

                    return a;
                })(),
                (() => {
                    const span = document.createElement('span');
                    span.textContent = this.translate('Stream', 'scopeNames');

                    return span;
                })(),
                typeText,
            ]);
        }

        return this.buildHeaderHtml([
            (() => {
                const span = document.createElement('span');
                span.textContent = this.translate('Stream', 'scopeNames');

                return span;
            })(),
            typeText,
        ]);
    }

    /**
     * @private
     */
    async actionFullRefresh() {
        Espo.Ui.notifyWait();

        await this.model.fetch();

        Espo.Ui.notify();
    }

    onRemove() {
        super.onRemove();

        if (this.webSocketManager.isEnabled()) {
            this.webSocketManager.unsubscribe(`recordUpdate.Note.${this.model.id}`);
        }
    }

    setupWebSocket() {
        if (!this.webSocketManager.isEnabled()) {
            return;
        }

        this.webSocketDebounceHelper = new DebounceHelper({
            handler: () => this.handleRecordUpdate(),
        });

        const topic = `recordUpdate.Note.${this.model.id}`;

        this.webSocketManager.subscribe(topic, () => this.webSocketDebounceHelper.process());
    }

    /**
     * @private
     */
    async handleRecordUpdate() {
        await this.model.fetch({highlight: true});
    }
}

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

import SiteNavbarItemView from 'views/site/navbar/item';
import View from 'view';
import ListRecordView from 'views/record/list';
import WindowPanelHelper from 'helpers/site/window-panel-helper';

// noinspection JSUnusedGlobalSymbols
export default class LastViewedSiteNavbarItemView extends SiteNavbarItemView {

    // language=Handlebars
    templateContent = `
        <a
            role="button"
            tabindex="0"
            data-action="showLastViewed"
            title="{{translate 'LastViewed' category='scopeNamesPlural'}}"
        ><span class="fas fa-clock-rotate-left fa-sm icon"></span></a>
        <div class="last-viewed-panel-container"></div>
    `

    /**
     * @private
     * @type {HTMLDivElement|null}
     */
    panelElement = null

    isAvailable() {
        return !this.getConfig().get('actionHistoryDisabled');
    }

    setup() {
        this.addActionHandler('showLastViewed', () => this.showPanel());

        this.onMouseUpBind = this.onMouseUp.bind(this);
        this.onClickBind = this.onClick.bind(this);
    }

    /**
     * @private
     */
    async showPanel() {
        const container = this.element.querySelector('.last-viewed-panel-container');

        if (!(container instanceof HTMLDivElement)) {
            this.panelElement = null;

            throw Error("No last-viewed-panel-container container.");
        }

        const panel = document.createElement('div');
        panel.id = 'last-viewed-panel';

        this.panelElement = panel;

        container.append(panel);

        const view = new PanelView({
            onClose: () => this.closePanel(),
        });

        await this.assignView('panel', view, '#last-viewed-panel');
        await view.render();

        new WindowPanelHelper(view);

        document.addEventListener('mouseup', this.onMouseUpBind);
        document.addEventListener('click', this.onClickBind);

        this.element.classList.add('open');

        await view.processFetch();
    }

    /**
     * @private
     */
    closePanel() {
        if (this.hasView('panel')) {
            this.getView('panel').remove();
        }

        this.element.classList.remove('open');

        this.panelElement?.parentElement?.removeChild(this.panelElement);

        document.removeEventListener('mouseup', this.onMouseUpBind);
        document.removeEventListener('click', this.onClickBind);
    }

    /**
     * @param {MouseEvent} e
     * @private
     */
    onMouseUp(e) {
        if (e.button !== 0) {
            return;
        }

        const target = e.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (
            this.panelElement === target ||
            this.panelElement.contains(target) ||
            target.classList.contains('modal') ||
            target.closest('.dialog.modal')
        ) {
            return;
        }

        return this.closePanel();
    }

    /**
     * @param {MouseEvent} e
     * @private
     */
    onClick(e) {
        const target = e.target;

        if (!(target instanceof HTMLAnchorElement)) {
            return;
        }

        if (
            target.dataset.action === 'showMore' ||
            target.dataset.action === 'showLastViewed'
        ) {
            return;
        }

        setTimeout(() => this.closePanel(), 100);
    }
}

class PanelView extends View {

    // language=Handlebars
    templateContent = `
        <div class="panel panel-default">
            <div class="panel-heading panel-heading-no-title">
            <div class="link-group">
                <a
                    role="button"
                    tabindex="0"
                    class="close-link"
                    data-action="closePanel"
                ><span class="fas fa-times"></span></a>
            </div>
            <a
                href="#LastViewed"
                class="text-solid"
            >{{translate 'LastViewed' category='scopeNamesPlural'}}</a>
            </div>
            <div class="panel-body">
                <div class="list-container">
                    <span class="text-soft fas fa-spinner fa-spin"></span>
                </div>
            </div>
        </div>
    `

    /**
     *
     * @param {{
     *     onClose: function(),
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        this.addActionHandler('closePanel', () => this.options.onClose());

        this.once('remove', () => {

        });
    }

    onRemove() {
        this.collection?.abortLastFetch();
    }

    async processFetch() {
        const collection = await this.getCollectionFactory().create('ActionHistoryRecord');
        collection.maxSize = this.getConfig().get('globalSearchMaxSize') ?? 10;
        collection.url = 'LastViewed';

        this.collection = collection;

        await collection.fetch();

        const view = new ListRecordView({
            collection: collection,
            fullSelector: this.containerSelector + ' .list-container',
            selectable: false,
            checkboxes: false,
            massActionsDisabled: true,
            rowActionsView: false,
            checkAllResultDisabled: true,
            buttonsDisabled: true,
            headerDisabled: true,
            layoutName: 'listForLastViewed',
            layoutAclDisabled: true,
        });

        await this.assignView('list', view, '.list-container');
        await view.render();
    }
}

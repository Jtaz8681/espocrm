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

class AdminIndexView extends View {

    template = 'admin/index'

    events = {
        /** @this AdminIndexView */
        'click [data-action]': function (e) {
            Espo.Utils.handleAction(this, e.originalEvent, e.currentTarget);
        },
        /** @this AdminIndexView */
        'keyup input[data-name="quick-search"]': function (e) {
            this.processQuickSearch(e.currentTarget.value);
        },
    }

    data() {
        return {
            panelDataList: this.panelDataList,
            iframeUrl: this.iframeUrl,
            iframeHeight: this.getConfig().get('adminPanelIframeHeight') || 1330,
            iframeDisabled: this.getConfig().get('adminPanelIframeDisabled') || false,
        };
    }

    afterRender() {
        const $quickSearch = this.$el.find('input[data-name="quick-search"]');

        if (this.quickSearchText) {
            $quickSearch.val(this.quickSearchText);

            this.processQuickSearch(this.quickSearchText);
        }

        // noinspection JSUnresolvedReference
        $quickSearch.get(0).focus({preventScroll: true});
    }

    setup() {
        this.panelDataList = [];

        const panels = this.getMetadata().get('app.adminPanel') || {};

        for (const name in panels) {
            const panelItem = Espo.Utils.cloneDeep(panels[name]);

            panelItem.name = name;
            panelItem.itemList = panelItem.itemList || [];
            panelItem.label = this.translate(panelItem.label, 'labels', 'Admin');

            if (panelItem.labelTranslation) {
                panelItem.label = this.getLanguage().translatePath(panelItem.labelTranslation);
            }

            if (panelItem.itemList) {
                panelItem.itemList.forEach(item => {
                    item.label = this.translate(item.label, 'labels', 'Admin');

                    if (item.labelTranslation) {
                        item.label = this.getLanguage().translatePath(item.labelTranslation);
                    }

                    if (item.description) {
                        item.keywords = (this.getLanguage().get('Admin', 'keywords', item.description) || '')
                            .split(',');

                        item.keywords = item.keywords.map(keyword => keyword.trim().toLowerCase());
                    } else {
                        item.keywords = [];
                    }
                });
            }

            // Legacy support.
            if (panelItem.items) {
                panelItem.items.forEach(item => {
                    item.label = this.translate(item.label, 'labels', 'Admin');
                    panelItem.itemList.push(item);

                    item.keywords = [];
                });
            }

            this.panelDataList.push(panelItem);
        }

        this.panelDataList.sort((v1, v2) => {
            if (!('order' in v1) && ('order' in v2)) {
                return 0;
            }

            if (!('order' in v2)) {
                return 0;
            }

            return v1.order - v2.order;
        });

        const iframeParams = [
            'version=' + encodeURIComponent(this.getConfig().get('version')),
            'css=' + encodeURIComponent(this.getConfig().get('siteUrl') +
                '/' + this.getThemeManager().getStylesheet())
        ];

        this.iframeUrl = this.getConfig().get('adminPanelIframeUrl') || 'https://s.espocrm.com/';

        if (~this.iframeUrl.indexOf('?')) {
            this.iframeUrl += '&' + iframeParams.join('&');
        } else {
            this.iframeUrl += '?' + iframeParams.join('&');
        }

        if (!this.getConfig().get('adminNotificationsDisabled')) {
            this.createView('notificationsPanel', 'views/admin/panels/notifications', {
                selector: '.notifications-panel-container'
            });
        }
    }

    processQuickSearch(text) {
        text = text.trim();

        this.quickSearchText = text;

        const $noData = this.$noData || this.$el.find('.no-data');

        $noData.addClass('hidden');

        if (!text) {
            this.$el.find('.admin-content-section').removeClass('hidden');
            this.$el.find('.admin-content-row').removeClass('hidden');

            return;
        }

        text = text.toLowerCase();

        this.$el.find('.admin-content-section').addClass('hidden');
        this.$el.find('.admin-content-row').addClass('hidden');

        let anythingMatched = false;

        this.panelDataList.forEach((panel, panelIndex) => {
            let panelMatched = false;
            let panelLabelMatched = false;

            if (panel.label && panel.label.toLowerCase().indexOf(text) === 0) {
                panelMatched = true;
                panelLabelMatched = true;
            }

            panel.itemList.forEach((row, rowIndex) => {
                if (!row.label) {
                    return;
                }

                let matched = false;

                if (panelLabelMatched) {
                    matched = true;
                }

                if (!matched) {
                    matched = row.label.toLowerCase().indexOf(text) === 0;
                }

                if (!matched) {
                    const wordList = row.label.split(' ');

                    wordList.forEach((word) => {
                        if (word.toLowerCase().indexOf(text) === 0) {
                            matched = true;
                        }
                    });

                    if (!matched) {
                        matched = ~row.keywords.indexOf(text);
                    }

                    if (!matched) {
                        if (text.length >= 3) {
                            row.keywords.forEach((word) => {
                                if (word.indexOf(text) === 0) {
                                    matched = true;
                                }
                            });
                        }
                    }
                }

                if (matched) {
                    panelMatched = true;

                    this.$el.find(
                        '.admin-content-section[data-index="'+panelIndex.toString()+'"] '+
                        '.admin-content-row[data-index="'+rowIndex.toString()+'"]'
                    ).removeClass('hidden');

                    anythingMatched = true;
                }
            });

            if (panelMatched) {

                this.$el
                    .find('.admin-content-section[data-index="' + panelIndex.toString() + '"]')
                    .removeClass('hidden');

                anythingMatched = true;
            }
        });

        if (!anythingMatched) {
            $noData.removeClass('hidden');
        }
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Administration'));
    }

    // noinspection JSUnusedGlobalSymbols
    actionClearCache() {
        this.trigger('clear-cache');
    }

    // noinspection JSUnusedGlobalSymbols
    actionRebuild() {
        this.trigger('rebuild');
    }
}

export default AdminIndexView;

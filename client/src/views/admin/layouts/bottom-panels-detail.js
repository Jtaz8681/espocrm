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

import LayoutSidePanelsDetailView from 'views/admin/layouts/side-panels-detail';

class LayoutBottomPanelsDetail extends LayoutSidePanelsDetailView {

    hasStream = true
    hasRelationships = true

    TAB_BREAK_KEY = '_tabBreak_{n}'

    setup() {
        super.setup();

        this.on('update-item', (name, attributes) => {
            if (this.isTabName(name)) {
                const $li = $("#layout ul > li[data-name='" + name + "']");

                $li.find('.left > span')
                    .text(this.composeTabBreakLabel(attributes));
            }
        });
    }

    composeTabBreakLabel(item) {
        let label = '. . . ' + this.translate('tabBreak', 'fields', 'LayoutManager');

        if (item.tabLabel) {
            label += ' : ' + item.tabLabel;
        }

        return label;
    }

    /**
     * @protected
     * @param {Record.<string, Record>} layout
     */
    readDataFromLayout(layout) {
        const data = this.getDataFromLayout(layout, 'bottomPanels', () => {
            const panelListAll = [];
            const labels = {};
            const params = {};

            if (
                this.hasStream &&
                (this.getMetadata().get(`scopes.${this.scope}.stream`) || this.scope === 'User')
            ) {
                panelListAll.push('stream');

                labels['stream'] = this.translate('Stream');

                params['stream'] = {
                    name: 'stream',
                    sticked: false,
                    index: 2,
                };
            }

            this.links = {};

            if (this.hasRelationships) {
                /** @type {Record<string, Record>} */
                const linkDefs = this.getMetadata().get(`entityDefs.${this.scope}.links`) || {};

                Object.keys(linkDefs).forEach(link => {
                    if (
                        linkDefs[link].disabled ||
                        linkDefs[link].utility ||
                        linkDefs[link].layoutRelationshipsDisabled
                    ) {
                        return;
                    }

                    if (!['hasMany', 'hasChildren'].includes(linkDefs[link].type)) {
                        return;
                    }

                    panelListAll.push(link);

                    labels[link] = this.translate(link, 'links', this.scope);

                    const item = {
                        name: link,
                        index: 5,
                    };

                    this.dataAttributeList.forEach(attribute => {
                        if (attribute in item) {
                            return;
                        }

                        const value = this.getMetadata()
                            .get(['clientDefs', this.scope, 'relationshipPanels', item.name, attribute]);

                        if (value === null) {
                            return;
                        }

                        item[attribute] = value;
                    });

                    this.links[link] = true;

                    params[item.name] = item;

                    if (!(item.name in layout)) {
                        item.disabled = true;
                    }
                });
            }

            panelListAll.push(this.TAB_BREAK_KEY);

            labels[this.TAB_BREAK_KEY] = '. . . ' + this.translate('tabBreak', 'fields', 'LayoutManager');
            params[this.TAB_BREAK_KEY] = {disabled: true};

            for (const name in layout) {
                const item = layout[name];

                if (item.tabBreak) {
                    panelListAll.push(name);

                    labels[name] = this.composeTabBreakLabel(item);

                    params[name] = {
                        name: item.name,
                        index: item.index,
                        tabBreak: true,
                        tabLabel: item.tabLabel || null,
                    };
                }
            }

            return {panelListAll, labels, params};
        });

        this.disabledFields = data.disabledFields;
        this.rowLayout = data.rowLayout;
        this.itemsData = data.itemsData;
    }

    onDrop() {
        let tabBreakIndex = -1;

        let $tabBreak = null;

        this.$el.find('ul.enabled').children().each((i, li) => {
            const $li = $(li);
            const name = $li.attr('data-name');

            if (this.isTabName(name)) {
                if (name !== this.TAB_BREAK_KEY) {
                    const itemIndex = parseInt(name.split('_')[2]);

                    if (itemIndex > tabBreakIndex) {
                        tabBreakIndex = itemIndex;
                    }
                }
            }
        });

        tabBreakIndex++;

        this.$el.find('ul.enabled').children().each((i, li) => {
            const $li = $(li);
            const name = $li.attr('data-name');

            if (this.isTabName(name) && name === this.TAB_BREAK_KEY) {
                $tabBreak = $li.clone();

                const realName = this.TAB_BREAK_KEY.slice(0, -3) + tabBreakIndex;

                $li.attr('data-name', realName);

                delete this.itemsData[realName];
            }
        });

        if (!$tabBreak) {
            this.$el.find('ul.disabled').children().each((i, li) => {
                const $li = $(li);

                const name = $li.attr('data-name');

                if (this.isTabName(name) && name !== this.TAB_BREAK_KEY) {
                    $li.remove();
                }
            });
        }

        if ($tabBreak) {
            $tabBreak.prependTo(this.$el.find('ul.disabled'));
        }
    }

    isTabName(name) {
        return name.substring(0, this.TAB_BREAK_KEY.length - 3) === this.TAB_BREAK_KEY.slice(0, -3);
    }

    getEditAttributesModalViewOptions(attributes) {
        const options = super.getEditAttributesModalViewOptions(attributes);

        if (this.isTabName(attributes.name)) {
            options.attributeList = [
                'tabLabel',
            ];

            options.attributeDefs = {
                tabLabel: {
                    type: 'varchar',
                },
            };
        }

        return options;
    }

    fetch() {
        const layout = super.fetch();

        const newLayout = {};

        for (const name in layout) {
            if (layout[name].disabled && this.links[name]) {
                continue;
            }

            newLayout[name] = layout[name];

            if (this.isTabName(name) && name !== this.TAB_BREAK_KEY /*&& this.itemsData[name]*/) {
                const data = this.itemsData[name] || {};

                newLayout[name].tabBreak = true;
                newLayout[name].tabLabel = data.tabLabel;
            }
            else {
               delete newLayout[name].tabBreak;
               delete newLayout[name].tabLabel;
            }
        }

        delete newLayout[this.TAB_BREAK_KEY];

        return newLayout;
    }


    validate(layout) {
        if (!super.validate(layout)) {
            return false;
        }


        return true;
    }
}

export default LayoutBottomPanelsDetail;

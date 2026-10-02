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

/**
 * @module views/record/row-actions/actions
 */

/**
 * @typedef {{
 *     label?: string,
 *     labelTranslation?: string,
 *     acl?: string,
 *     groupIndex?: number,
 *     name?: string,
 *     text?: string,
 *     html?: string,
 *     viewKey?: string,
 *     iconClass?: string,
 * }} module:views/record/row-actions/actions~item
 */

/**
 * Row actions.
 */
class DefaultRowActionsView extends View {

    template = 'record/row-actions/default'

    /**
     * @private
     * @type {boolean}
     */
    menuIsShown = false

    /**
     * @private
     * @type {module:views/record/row-actions/actions~item[]}
     */
    lastActionList

    /**
     * @private
     * @type {Object.<string, {isAvailable: function(module:model, string)}>}
     */
    handlers

    static ICON_CLASS_VIEW = 'fas fa-expand';
    static ICON_CLASS_EDIT= 'fas fa-pen-to-square';
    static ICON_CLASS_REMOVE = 'fas fa-times';

    /**
     * @param {{
     *    acl?: {
     *        edit?: boolean,
     *        detete?: boolean,
     *    },
     *    model: import('model').default,
     *    rowActionHandlers?: Object.<string, {isAvailable: function(module:model, string)}>,
     *    additionalActionList?: string[],
     *    scope?: string,
     * } & Record} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        this.options.acl = this.options.acl || {};
        this.scope = this.options.scope || this.model.entityType;

        // noinspection JSValidateTypes
        this.handlers = this.options.rowActionHandlers || {};

        /** @type {module:views/record/row-actions/actions~item[]} */
        this.additionalActionDataList = [];

        this.setupAdditionalActions();

        const handleReRender = (/** Record */o) => {
            if (o.keepRowActions) {
                return;
            }

            if (this.menuIsShown) {
                this.once('menu-hidden', () => this.reRender({buffer: true}));

                return;
            }

            this.reRender({buffer: true});
        };

        this.listenTo(this.model, 'change', (m, /** Record */o) => handleReRender(o));

        if (this.model.collection && this.model.collection.parentModel) {
            // Access to actions can be defined by a parent model.
            this.listenTo(this.model.collection.parentModel, 'sync', (m, r, /** Record */o) => {
                if (!this.lastActionList) {
                    return;
                }

                setTimeout(() => {
                    if (Espo.Utils.areEqual(this.lastActionList, this.getActionList())) {
                        return true;
                    }

                    handleReRender(o);
                }, 0);
            });
        }
    }

    afterRender() {
        this.menuIsShown = false;

        const $dd = this.$el.find('button[data-toggle="dropdown"]').parent();

        let isChecked = false;

        $dd.on('show.bs.dropdown', () => {
            const $el = this.$el.closest('.list-row');

            isChecked = false;

            if ($el.hasClass('active')) {
                isChecked = true;
            }

            $el.addClass('active');

            this.menuIsShown = true;
        });

        $dd.on('hide.bs.dropdown', () => {
            if (!isChecked) {
                this.$el.closest('.list-row').removeClass('active');
            }

            this.menuIsShown = false;
            this.trigger('menu-hidden');
        });
    }

    /**
     * Get an action list.
     *
     * @return {import('views/record/list').RowAction[]}
     */
    getActionList() {
        /** @type {import('views/record/list').RowAction[]} */
        const list = [{
            action: 'quickView',
            label: 'View',
            data: {
                id: this.model.id
            },
            link: `#${this.model.entityType}/view/${this.model.id}`,
            groupIndex: 0,
            iconClass: DefaultRowActionsView.ICON_CLASS_VIEW,
        }];

        if (this.checkAccess('edit')) {
            list.push({
                action: 'quickEdit',
                label: 'Edit',
                data: {
                    id: this.model.id
                },
                link: `#${this.model.entityType}/edit/${this.model.id}`,
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_EDIT,
            });
        }

        this.getAdditionalActionList().forEach(item => list.push(item));

        if (this.checkAccess('delete')) {
            list.push({
                action: 'quickRemove',
                label: 'Remove',
                data: {
                    id: this.model.id,
                },
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_REMOVE,
            });
        }

        return list;
    }

    /**
     * Not to be overridden.
     *
     * @protected
     * @return {import('views/record/list').RowAction[]}
     */
    getAdditionalActionList() {
        const list = [];

        this.additionalActionDataList.forEach(item => {
            const handler = this.handlers[item.name];

            if (handler && !handler.isAvailable(this.model, item.name)) {
                return;
            }

            if (item.acl && item.acl !== 'read') {
                if (!this.getAcl().checkModel(this.model, item.acl)) {
                    return;
                }
            }

            list.push({
                action: 'rowAction',
                text: item.text,
                data: {
                    id: this.model.id,
                    actualAction: item.name,
                },
                groupIndex: item.groupIndex,
                iconClass: item.iconClass,
            });
        });

        return list;
    }

    data() {
        /** @type {Array<module:views/record/row-actions/actions~item[]>} */
        const dropdownGroups = [];

        const actionList = this.getActionList();

        this.lastActionList = actionList;

        actionList.forEach(item => {
            // For bc.
            if (item === false) {
                return;
            }

            const index = (item.groupIndex === undefined ? 9999 : item.groupIndex) + 100;

            if (dropdownGroups[index] === undefined) {
                dropdownGroups[index] = [];
            }

            dropdownGroups[index].push(item);
        });

        const dropdownItemList = [];

        dropdownGroups.forEach(list => {
            list.forEach(it => dropdownItemList.push(it));

            dropdownItemList.push(false);
        });

        return {
            acl: this.options.acl,
            actionList: dropdownItemList,
            scope: this.model.entityType,
        };
    }

    setupAdditionalActions() {
        const list = this.options.additionalActionList;

        if (!list) {
            return;
        }

        const defs = this.getMetadata().get(`clientDefs.${this.scope}.rowActionDefs`) || {};

        list.forEach(action => {
            /**
             * @type {{
             *     label?: string,
             *     labelTranslation?: string,
             *     acl?: string,
             *     groupIndex?: number,
             *     iconClass?: string,
             * }}
             */
            const itemDefs = defs[action] || {};

            const text = itemDefs.labelTranslation ?
                this.getLanguage().translatePath(itemDefs.labelTranslation) :
                this.getLanguage().translate(itemDefs.label, 'labels', this.model.entityType);

            this.additionalActionDataList.push({
                name: action,
                acl:  itemDefs.acl,
                text: text,
                groupIndex: itemDefs.groupIndex,
                iconClass: itemDefs.iconClass,
            });
        });
    }

    /**
     * @protected
     * @param {string} action
     * @retyrn {boolean}
     * @since 9.0.0
     */
    checkAccess(action) {
        if (typeof this.options.acl[action] === 'boolean') {
            return this.options.acl[action];
        }

        return this.getAcl().checkModel(this.model, action);
    }
}

export default DefaultRowActionsView;

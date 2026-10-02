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

import {inject} from 'di';
import Metadata from 'metadata';

/**
 * @internal
 */
class SelectRelatedHelper {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @param {import('view').default} view
     */
    constructor(view) {
        /** @private */
        this.view = view;
    }

    /**
     * @param {import('model').default} model
     * @param {string} link
     * @param {{
     *     foreignEntityType?: string,
     *     massSelect?: boolean,
     *     primaryFilterName?: string,
     *     boolFilterList?: string[]|string,
     *     viewKey?: string,
     *     hasCreate?: boolean,
     *     onCreate?: function(): void,
     * }} options
     */
    process(model, link, options = {}) {
        if (!options.foreignEntityType && !model.defs['links'][link]) {
            throw new Error(`Link ${link} does not exist.`);
        }

        const scope = options.foreignEntityType || model.defs['links'][link].entity;

        /** @var {Object.<string, *>} */
        const panelDefs = this.metadata.get(['clientDefs', model.entityType, 'relationshipPanels', link]) || {};

        const massRelateEnabled = options.massSelect || panelDefs.massSelect;

        let advanced = {};

        const foreignLink = model.getLinkParam(link, 'foreign');

        if (foreignLink && scope) {
            // Select only records not related with any.
            const foreignLinkType = this.metadata.get(['entityDefs', scope, 'links', foreignLink, 'type']);
            const foreignLinkFieldType = this.metadata.get(['entityDefs', scope, 'fields', foreignLink, 'type']);

            if (
                ['belongsTo', 'belongsToParent'].includes(foreignLinkType) &&
                foreignLinkFieldType &&
                !advanced[foreignLink] &&
                ['link', 'linkParent'].includes(foreignLinkFieldType)
            ) {
                advanced[foreignLink] = {
                    type: 'isNull',
                    attribute: foreignLink + 'Id',
                    data: {
                        type: 'isEmpty',
                    },
                };
            }
        }

        let primaryFilterName = options.primaryFilterName || null;

        if (typeof primaryFilterName === 'function') {
            primaryFilterName = primaryFilterName.call(this);
        }

        let dataBoolFilterList = options.boolFilterList;

        if (typeof options.boolFilterList === 'string') {
            dataBoolFilterList = options.boolFilterList.split(',');
        }

        let boolFilterList = dataBoolFilterList || panelDefs.selectBoolFilterList;

        if (typeof boolFilterList === 'function') {
            boolFilterList = boolFilterList.call(this);
        }

        boolFilterList = Espo.Utils.clone(boolFilterList);

        primaryFilterName = primaryFilterName || panelDefs.selectPrimaryFilterName || null;

        const viewKey = options.viewKey || 'select';

        const viewName = panelDefs.selectModalView ||
            this.metadata.get(['clientDefs', scope, 'modalViews', viewKey]) ||
            'views/modals/select-records';

        Espo.Ui.notifyWait();

        const handler = panelDefs.selectHandler || null;

        new Promise(resolve => {
            if (!handler) {
                resolve({});

                return;
            }

            Espo.loader.requirePromise(handler)
                .then(Handler => new Handler(this.view.getHelper()))
                .then(/** import('contracts/relation').SelectRelatedHandler */handler => {
                    handler.getFilters(model)
                        .then(filters => resolve(filters));
                });
        }).then(filters => {
            advanced = {...advanced, ...(filters.advanced || {})};

            if (boolFilterList || filters.bool) {
                boolFilterList = [
                    ...(boolFilterList || []),
                    ...(filters.bool || []),
                ];
            }

            if (filters.primary && !primaryFilterName) {
                primaryFilterName = filters.primary;
            }

            const orderBy = filters.orderBy || panelDefs.selectOrderBy;
            const orderDirection = filters.orderBy ? filters.order : panelDefs.selectOrderDirection;

            const createButton = options.hasCreate === true && options.onCreate !== undefined;

            /** @type {import('views/modals/select-records').default} */
            let modalView;

            this.view.createView('dialogSelectRelated', viewName, {
                scope: scope,
                multiple: true,
                filters: advanced,
                massRelateEnabled: massRelateEnabled,
                primaryFilterName: primaryFilterName,
                boolFilterList: boolFilterList,
                mandatorySelectAttributeList: panelDefs.selectMandatoryAttributeList,
                layoutName: panelDefs.selectLayout,
                orderBy: orderBy,
                orderDirection: orderDirection,
                createButton: createButton,
                onCreate: () => {
                    modalView.close();

                    if (options.onCreate) {
                        options.onCreate();
                    }
                },
            }, view => {
                modalView = view;

                view.render();

                Espo.Ui.notify(false);

                this.view.listenToOnce(view, 'select', (selectObj) => {
                    const data = {};

                    if (Object.prototype.toString.call(selectObj) === '[object Array]') {
                        const ids = [];

                        selectObj.forEach(model => ids.push(model.id));

                        data.ids = ids;
                    }  else if (selectObj.massRelate) {
                        data.massRelate = true;
                        data.where = selectObj.where;
                        data.searchParams = selectObj.searchParams;
                    } else {
                        data.id = selectObj.id;
                    }

                    const url = `${model.entityType}/${model.id}/${link}`;

                    Espo.Ajax.postRequest(url, data)
                        .then(() => {
                            Espo.Ui.success(this.view.translate('Linked'))

                            model.trigger(`update-related:${link}`);

                            model.trigger('after:relate');
                            model.trigger(`after:relate:${link}`);
                        });
                });
            });
        });
    }
}

export default SelectRelatedHelper;

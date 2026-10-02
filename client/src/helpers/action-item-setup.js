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
import ViewHelper from 'view-helper';
import AclManager from 'acl-manager';
import Language from 'language';

/** @module helpers/action-item-setup */

/**
 * @internal
 */
class ActionItemSetupHelper {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @private
     * @type {ViewHelper}
     */
    @inject(ViewHelper)
    viewHelper

    /**
     * @private
     * @type {AclManager}
     */
    @inject(AclManager)
    acl

    /**
     * @private
     * @type {Language}
     */
    @inject(Language)
    language

    /**
     * @param {{
     *     view: import('view').default<any>,
     *     type: string,
     *     waitFunc: (Promise) => void,
     *     addFunc: (
     *          item: import('views/record/detail').DropdownItem |
     *              import('views/record/detail').Button
 *         ) => void,
     *     showFunc: (string) => void,
     *     hideFunc: (string) => void,
     *     enableFunc?: (string) => void,
     *     disableFunc?: (string) => void,
     *     listenToViewModelSync?: boolean,
     *     syncEvent?: 'sync'|'change',
     * }} options
     */
    setup(options) {
        const view = options.view;
        const type = options.type;
        const waitFunc = options.waitFunc;
        const addFunc = options.addFunc;
        const showFunc = options.showFunc;
        const hideFunc = options.hideFunc;
        const enableFunc = options.enableFunc ?? (() => {});
        const disableFunc = options.disableFunc ?? (() => {});

        /** @type {Record[]} */
        const actionList = [];

        // noinspection JSUnresolvedReference
        const scope = view.scope || view.model.entityType;

        if (!scope) {
            throw new Error();
        }

        const path = type.split('.');

        /** @type {({name?: string} & Record | string)[]} */
        const actionDefsListOriginal = [
            ...this.metadata.get(['clientDefs', 'Global', ...path], []),
            ...this.metadata.get(['clientDefs', scope, ...path], []),
        ];

        /** @type {({name?: string} & Record<string, any>)[]} */
        let actionDefsList = actionDefsListOriginal.map(item => {
            if (typeof item === 'string') {
                return {name: item};
            }

            return Espo.Utils.cloneDeep(item);
        })

        actionDefsList.reverse();

        actionDefsList = actionDefsList.filter((it, i, self) => {
            return self.findIndex(sIt => sIt.name === it.name) === i;
        });

        actionDefsList.reverse();

        actionDefsList.forEach(item => {
            const name = item.name;

            if (!item.label && !item.labelTranslation && !item.iconClass) {
                item.text = this.language.translate(name, 'actions', scope);
            }

            item.data = item.data || {};

            const handlerName = item.handler || item.data.handler;

            if (handlerName && !item.data.handler) {
                item.data.handler = handlerName;
            }

            if (!Espo.Utils.checkActionAvailability(this.viewHelper, item)) {
                return;
            }

            addFunc(item);

            if (!Espo.Utils.checkActionAccess(this.acl, view.model, item, true)) {
                item.hidden = true;
            }

            actionList.push(item);

            if (!handlerName) {
                return;
            }

            if (!item.initFunction && !item.checkVisibilityFunction && !item.checkAvailabilityFunction) {
                return;
            }

            waitFunc(new Promise(resolve => {
                Espo.loader.require(handlerName, Handler => {
                    const handler = new Handler(view);

                    if (item.initFunction) {
                        handler[item.initFunction].call(handler);
                    }

                    if (item.checkVisibilityFunction) {
                        const isNotVisible = !handler[item.checkVisibilityFunction].call(handler);

                        if (isNotVisible) {
                            hideFunc(item.name);
                        }
                    }

                    if (item?.checkAvailabilityFunction) {
                        const isNotAvailable = !handler[item.checkAvailabilityFunction].call(handler);

                        if (isNotAvailable) {
                            disableFunc(item.name);
                        }
                    }

                    item.handlerInstance = handler;

                    resolve();
                });
            }));
        });

        if (!actionList.length) {
            return;
        }

        const onChange = () => {
            actionList.forEach(item => {
                const handler = item.handlerInstance;

                if (!handler || !item.checkAvailabilityFunction) {
                    return;
                }
                const isAvailable = handler[item.checkAvailabilityFunction].call(handler);

                isAvailable ?
                    enableFunc(item.name) :
                    disableFunc(item.name);
            });
        }

        const onSync = () => {
            actionList.forEach(item => {
                const handler = item.handlerInstance;

                if (handler && item.checkVisibilityFunction) {
                    const isNotVisible = !handler[item.checkVisibilityFunction].call(handler);

                    if (isNotVisible) {
                        hideFunc(item.name);

                        return;
                    }
                }

                if (Espo.Utils.checkActionAccess(this.acl, view.model, item, true)) {
                    showFunc(item.name);

                    return;
                }

                hideFunc(item.name);
            });
        };

        if (options.listenToViewModelSync) {
            view.listenTo(view, 'model-sync', () => onSync());

            return;
        }

        view.listenTo(view.model, 'change', () => onChange());
        view.listenTo(view.model, options.syncEvent ?? 'sync', () => onSync());
    }
}

export default ActionItemSetupHelper;

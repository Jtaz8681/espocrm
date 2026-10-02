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

import {View as BullView} from 'bullbone';
import Model from 'model';
import Collection from 'collection';
import Ui from 'ui';
import type Preferences from 'models/preferences';
import type Settings from 'models/settings';
import type User from 'models/user';
import type ViewHelper from 'view-helper';
import type AclManager from 'acl-manager';
import type ModelFactory from 'model-factory';
import type CollectionFactory from 'collection-factory';
import type Router from 'router';
import type Storage from 'storage';
import type SessionStorage from 'session-storage';
import type Language from 'language';
import type Metadata from 'metadata';
import type Cache from 'cache';
import type DateTime from 'date-time';
import type NumberUtil from 'number-util';
import type FieldManager from 'field-manager';
import type BaseController from 'controllers/base';
import type ThemeManager from 'theme-manager';

export interface ConfirmOptions {
    message: string;
    confirmText?: string;
    cancelText?: string;
    confirmStyle?: 'danger' | 'success' | 'warning' | 'default';
    backdrop?: 'static' | boolean;
    cancelCallback?: () => void;
}

/**
 * @param {MouseEvent} event DOM event.
 * @param {HTMLElement} includeInactive A target element.
 */
type ActionHandlerCallback = (event: MouseEvent, element: HTMLElement) => void;

export interface ViewSchema {
    model?: Model;
    collection?: Collection;
    options?: Record<string, any>;
}

/**
 * A base view. All views should extend this class.
 *
 * @see https://docs.espocrm.com/development/view/
 */
export default class View<S extends ViewSchema = ViewSchema> extends BullView<S['model'], S['collection']> {

    /**
     * A model.
     */
    model: S['model']

    /**
     * A collection.
     */
    collection: S['collection']

    /**
     * Options.
     */
    options: Record<string, any> & S['options']

    /**
     * @param options Options.
     */
    constructor(
        options: Record<string, any> & S['options'] = {}
    ) {
        super(options);

        if (options.model) {
            this.model = options.model;
        }

        if (options.collection) {
            this.collection = options.collection;
        }
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * When the view is ready. Can be useful to prevent race condition when re-initialization is needed
     * in-between initialization and render.
     *
     * @todo Move to Bull.View.
     */
    whenReady(): Promise<void> {
        if (this.isReady) {
            return Promise.resolve();
        }

        return new Promise<void>(resolve => {
            this.once('ready', () => resolve());
        });
    }

    /**
     * Add a DOM click event handler for a target defined by `data-action="{name}"` attribute.
     *
     * @param action An action name.
     * @param handler A handler.
     */
    addActionHandler(action: string, handler: ActionHandlerCallback) {
        // The key should be in sync with one in Utils.handleAction.
        const fullAction = `click [data-action="${action}"]`;

        this.events[fullAction] = e => {
            // noinspection JSUnresolvedReference
            handler.call(this, e.originalEvent as MouseEvent, e.currentTarget as HTMLElement);
        };
    }

    /**
     * Escape a string.
     */
    escapeString(string: string): string {
        return Handlebars.Utils.escapeExpression(string);
    }

    /**
     * Show a notify-message.
     *
     * @deprecated Use `Espo.Ui.notify`.
     * @param {string|false} label
     * @param {string} [type]
     * @param {number} [timeout]
     * @param {string} [scope]
     */
    notify(label: string | false, type: string, timeout: number, scope: string) {
        if (!label) {
            // @ts-ignore
            Ui.notify(false);

            return;
        }

        let timeoutInternal : number | undefined = timeout || 2000;

        if (!type) {
            timeoutInternal = undefined;
        }

        const text = this.getLanguage().translate(label, 'labels', scope || undefined);

        // @ts-ignore
        Ui.notify(text, type, timeoutInternal);
    }

    /**
     * Get a view-helper.
     */
    getHelper(): ViewHelper {
        return this._helper as ViewHelper;
    }

    /**
     * Get a current user.
     */
    getUser(): User {
        // @ts-ignore
        return this._helper.user;
    }

    /**
     * Get the preferences.
     */
    getPreferences(): Preferences {
        // @ts-ignore
        return this._helper.preferences;
    }

    /**
     * Get the config.
     */
    getConfig(): Settings {
        // @ts-ignore
        return this._helper.settings;
    }

    /**
     * Get the ACL.
     */
    getAcl(): AclManager {
        // @ts-ignore
        return this._helper.acl;
    }

    /**
     * Get the model factory.
     */
    getModelFactory(): ModelFactory {
        // @ts-ignore
        return this._helper.modelFactory;
    }

    /**
     * Get the collection factory.
     */
    getCollectionFactory(): CollectionFactory{
        // @ts-ignore
        return this._helper.collectionFactory;
    }

    /**
     * Get the router.
     */
    getRouter(): Router {
        // @ts-ignore
        return this._helper.router;
    }

    /**
     * Get the storage-util.
     */
    getStorage(): Storage {
        // @ts-ignore
        return this._helper.storage;
    }

    /**
     * Get the session-storage-util.
     */
    getSessionStorage(): SessionStorage{
        // @ts-ignore
        return this._helper.sessionStorage;
    }

    /**
     * Get the language-util.
     */
    getLanguage(): Language {
        // @ts-ignore
        return this._helper.language;
    }

    /**
     * Get metadata.
     */
    getMetadata(): Metadata {
        // @ts-ignore
        return this._helper.metadata;
    }

    /**
     * Get the cache-util.
     */
    getCache(): Cache {
        // @ts-ignore
        return this._helper.cache;
    }

    /**
     * Get the date-time util.
     */
    getDateTime(): DateTime {
        // @ts-ignore
        return this._helper.dateTime;
    }

    /**
     * Get the number-util.
     */
    getNumberUtil(): NumberUtil{
        // @ts-ignore
        return this._helper.numberUtil;
    }

    /**
     * Get the field manager.
     */
    getFieldManager(): FieldManager {
        // @ts-ignore
        return this._helper.fieldManager;
    }

    /**
     * Get the base-controller.
     *
     * @internal
     */
    getBaseController(): BaseController {
        // @ts-ignore
        return this._helper.baseController;
    }

    /**
     * Get the theme manager.
     */
    getThemeManager(): ThemeManager {
        // @ts-ignore
        return this._helper.themeManager;
    }

    /**
     * Update a page title. Supposed to be overridden if needed.
     */
    updatePageTitle() {
        const title = this.getConfig().get('applicationName') || 'EspoCRM';

        this.setPageTitle(title);
    }

    /**
     * Set a page title.
     *
     * @param title A title.
     */
    protected setPageTitle(title: string) {
        this.getHelper().pageTitle.setTitle(title);
    }

    /**
     * Translate a label.
     *
     * @param label Label.
     * @param [category='labels'] Category.
     * @param [scope='Global'] Scope.
     * @returns {string}
     */
    translate(
        label: string,
        category?: string | 'messages' | 'labels' | 'fields' | 'links' | 'scopeNames' | 'scopeNamesPlural',
        scope?: string | null,
    ): string {

        return this.getLanguage().translate(label, category, scope);
    }

    /**
     * Get a base path.
     */
    getBasePath(): string {
        // @ts-ignore
        return this._helper.basePath || '';
    }

    /**
     * Show a confirmation dialog.
     *
     * @param options A message or options.
     * @param [callback] A callback. Deprecated, use a promise.
     * @param [context] A context. Deprecated.
     * @returns {Promise} To be resolved if confirmed.
     */
    confirm(options: ConfirmOptions, callback = undefined, context= undefined): Promise<any> {
        let message: string;

        let o: ConfirmOptions | Record<string, any>;

        if (typeof options === 'string' || options instanceof String) {
            message = options.toString();

            o = {};
        } else {
            o = options ?? {};

            message = options.message;
        }

        if (message) {
            message = this.getHelper()
                .transformMarkdownText(message, {linksInNewTab: true})
                .toString();
        }

        const confirmText = options.confirmText || this.translate('Yes');
        const confirmStyle = options.confirmStyle || null;
        const cancelText = options.cancelText || this.translate('Cancel');

        return Ui.confirm(message, {
            confirmText: confirmText,
            cancelText: cancelText,
            confirmStyle: confirmStyle ?? undefined,
            backdrop: ('backdrop' in o) ? o.backdrop : true,
            isHtml: true,
            cancelCallback: options.cancelCallback,
        }, callback, context);
    }
}

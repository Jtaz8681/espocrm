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

import MainView, {MainViewOptions} from 'views/main';
import Model from 'model';
import type View from 'view';

export interface EditViewSchema {
    model: Model;
    options: EditViewOptions;
}

export interface EditViewOptions extends MainViewOptions {
    rootUrl?: string;
    params?: {
        rootUrl?: string;
        rootData?: Record<string, unknown>;
        focusForCreate?: boolean;
    };
    recordView?: string;
}

type RecordView = View & {
    setupReuse?: () => void;
    getMode: () => 'detail' | 'edit';
}

/**
 * An edit view.
 */
class EditView<S extends EditViewSchema = EditViewSchema> extends MainView<S> {

    protected template: string = 'edit'

    readonly name = 'Edit'

    protected optionsToPass: string[] = [
        'returnUrl',
        'returnDispatchParams',
        'attributes',
        'rootUrl',
        'duplicateSourceId',
        'returnAfterCreate',
        'highlightFieldList',
    ]

    /**
     * A header view name.
     */
    protected headerView = 'views/header'

    /**
     * A record view name.
     */
    protected recordView = 'views/record/edit'

    /**
     * A root breadcrumb item not to be a link.
     */
    protected readonly rootLinkDisabled: boolean = false

    /**
     * A root URL.
     */
    protected rootUrl: string

    private nameAttribute: string = 'name'

    protected entityType: string | null

    protected setup() {
        if (!this.model.entityType) {
            throw new Error('No entity type.');
        }

        this.entityType = this.model.entityType;

        this.headerView = this.options.headerView || this.headerView;
        this.recordView = this.options.recordView || this.recordView;

        this.rootUrl = this.options.rootUrl ?? this.options.params?.rootUrl ?? this.rootUrl ?? `#${this.scope}`;

        if (this.entityType) {
            this.nameAttribute = this.getMetadata().get(`clientDefs.${this.entityType}.nameAttribute`) ??
                this.nameAttribute;
        }

        this.setupHeader();
        this.setupRecord();
    }

    protected setupFinal() {
        super.setupFinal();

        this.wait(
            this.getHelper().processSetupHandlers(this, 'edit')
        );
    }

    /**
     * Set up a header.
     */
    protected setupHeader() {
        this.createView('header', this.headerView, {
            model: this.model,
            fullSelector: '#main > .header',
            scope: this.scope,
        });
    }

    /**
     * Set up a record.
     */
    protected setupRecord() {
        const o = {
            model: this.model,
            fullSelector: '#main > .record',
            scope: this.scope,
            shortcutKeysEnabled: true,
        } as Record<string, unknown>;

        this.optionsToPass.forEach(option => {
            o[option] = this.options[option];
        });

        const params = this.options.params ?? {};

        o.rootUrl = this.rootUrl;

        if (params.rootData) {
            o.rootData = params.rootData;
        }

        if (params.focusForCreate) {
            o.focusForCreate = true;
        }

        return this.createView('record', this.getRecordViewName(), o);
    }

    protected getRecordView(): RecordView {
        return this.getView<RecordView>('record') as RecordView;
    }

    /**
     * Get a record view name.
     */
    private getRecordViewName(): string {
        return this.getMetadata().get('clientDefs.' + this.scope + '.recordViews.edit') ?? this.recordView;
    }

    getHeader(): string {
        const scopeLabel = this.getLanguage().translate(this.scope, 'scopeNamesPlural');

        let root = document.createElement('span');
        root.textContent = scopeLabel;
        root.style.userSelect = 'none';

        if (!this.options.noHeaderLinks && !this.rootLinkDisabled) {
            const a = document.createElement('a');
            a.href = this.rootUrl;
            a.classList.add('action');
            a.dataset.action = 'navigateToRoot';
            a.text = scopeLabel;

            root = document.createElement('span');
            root.style.userSelect = 'none';
            root.append(a);
        }

        const iconHtml = this.getHeaderIconHtml();

        if (iconHtml) {
            root.insertAdjacentHTML('afterbegin', iconHtml);
        }

        if (this.model.isNew()) {
            const create = document.createElement('span');
            create.textContent = this.getLanguage().translate('create');
            create.style.userSelect = 'none';

            return this.buildHeaderHtml([root, create]);
        }

        const name = this.model.attributes[this.nameAttribute] || this.model.id;

        let title = document.createElement('span');
        title.textContent = name;

        if (!this.options.noHeaderLinks) {
            const url = `#${this.scope}/view/${this.model.id}`;

            const a = document.createElement('a');
            a.href = url;
            a.classList.add('action');

            a.append(title);

            title = a;
        }

        return this.buildHeaderHtml([root, title]);
    }

    updatePageTitle() {
        if (this.model.isNew()) {
            const title = this.getLanguage().translate('Create') + ' ' +
                this.getLanguage().translate(this.scope, 'scopeNames');

            this.setPageTitle(title);

            return;
        }

        const name = this.model.attributes[this.nameAttribute];

        const title = name ? name : this.getLanguage().translate(this.scope, 'scopeNames');

        this.setPageTitle(title);
    }

    setupReuse(params: Record<string, unknown>) {
        // noinspection BadExpressionStatementJS
        params;

        const recordView = this.getRecordView();

        if (!recordView) {
            return;
        }

        if (!recordView.setupReuse) {
            return;
        }

        recordView.setupReuse();
    }
}

export default EditView;

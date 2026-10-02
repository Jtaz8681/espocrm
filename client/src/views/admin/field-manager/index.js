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

class IndexFieldManagerView extends View {

    template = 'admin/field-manager/index'
    scopeList = null
    scope = null
    type = null

    data() {
        return {
            scopeList: this.scopeList,
            scope: this.scope,
        };
    }

    events = {
        /** @this IndexFieldManagerView */
        'click #scopes-menu a.scope-link': function (e) {
            const scope = $(e.currentTarget).data('scope');

            this.openScope(scope);
        },
        /** @this IndexFieldManagerView */
        'click #fields-content a.field-link': function (e) {
            e.preventDefault();

            const scope = $(e.currentTarget).data('scope');
            const field = $(e.currentTarget).data('field');

            this.openField(scope, field);
        },
        /** @this IndexFieldManagerView */
        'click [data-action="addField"]': function () {
            this.createView('dialog', 'views/admin/field-manager/modals/add-field', {}, (view) => {
                view.render();

                this.listenToOnce(view, 'add-field', type => {
                    this.createField(this.scope, type);
                });
            });
        },
    }

    setup() {
        this.scopeList = [];

        const scopesAll = Object.keys(this.getMetadata().get('scopes')).sort((v1, v2) => {
            return this.translate(v1, 'scopeNamesPlural').localeCompare(this.translate(v2, 'scopeNamesPlural'));
        });

        scopesAll.forEach(scope => {
            if (
                this.getMetadata().get('scopes.' + scope + '.entity') &&
                this.getMetadata().get('scopes.' + scope + '.customizable')
            ) {
                this.scopeList.push(scope);
            }
        });

        this.scope = this.options.scope || null;
        this.field = this.options.field || null;

        this.on('after:render', () => {
            if (!this.scope) {
                this.renderDefaultPage();

                return;
            }

            if (!this.field) {
                this.openScope(this.scope);
            }
            else {
                this.openField(this.scope, this.field);
            }
        });

        this.createView('header', 'views/admin/field-manager/header', {
            selector: '> .page-header',
            scope: this.scope,
            field: this.field,
        });
    }

    openScope(scope) {
        this.scope = scope;
        this.field = null;

        this.getHeaderView().setField(null);

        this.getRouter().navigate('#Admin/fieldManager/scope=' + scope, {trigger: false});

        Espo.Ui.notifyWait();

        this.createView('content', 'views/admin/field-manager/list', {
            fullSelector: '#fields-content',
            scope: scope,
        }, (view) => {
            view.render();

            Espo.Ui.notify(false);

            $(window).scrollTop(0);
        });
    }

    /**
     *
     * @return {import('./header').default}
     */
    getHeaderView() {
        return this.getView('header');
    }

    openField(scope, field) {
        this.scope = scope;
        this.field = field;

        this.getHeaderView().setField(field);

        this.getRouter()
            .navigate('#Admin/fieldManager/scope=' + scope + '&field=' + field, {trigger: false});

        Espo.Ui.notifyWait();

        this.createView('content', 'views/admin/field-manager/edit', {
            fullSelector: '#fields-content',
            scope: scope,
            field: field,
        }, (view) => {
            view.render();

            Espo.Ui.notify(false);

            $(window).scrollTop(0);

            this.listenTo(view, 'after:save', () => {
                Espo.Ui.success(this.translate('Saved'));
            });
        });
    }

    /**
     * @private
     * @param {string} scope
     * @param {string} type
     */
    createField(scope, type) {
        this.scope = scope;
        this.type = type;

        this.getRouter()
            .navigate('#Admin/fieldManager/scope=' + scope + '&type=' + type + '&create=true', {trigger: false});

        Espo.Ui.notifyWait();

        this.createView('content', 'views/admin/field-manager/edit', {
            fullSelector: '#fields-content',
            scope: scope,
            type: type,
        }, view => {
            view.render();

            Espo.Ui.notify(false);
            $(window).scrollTop(0);

            view.once('after:save', () => {
                this.openScope(this.scope);

                if (!this.getMetadata().get(`scopes.${this.scope}.layouts`)) {
                    Espo.Ui.success(this.translate('Created'), {suppress: true});

                    return;
                }

                const message = this.translate('fieldCreatedAddToLayouts', 'messages', 'FieldManager')
                    .replace('{link}', `#Admin/layouts/scope=${this.scope}&em=true`);

                setTimeout(() => {
                    Espo.Ui.notify(message, 'success', undefined, {closeButton: true});
                }, 100);
            });
        });
    }

    renderDefaultPage() {
        $('#fields-content').html(this.translate('selectEntityType', 'messages', 'Admin'));
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Field Manager', 'labels', 'Admin'));
    }
}

export default IndexFieldManagerView;

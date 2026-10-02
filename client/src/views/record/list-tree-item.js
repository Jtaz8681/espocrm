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

/** @module views/record/list-tree-item */

import View from 'view';

class ListTreeRecordItemView extends View {

    template = 'record/list-tree-item'

    isEnd = false
    level = 0
    listViewName = 'views/record/list-tree'

    /**
     * @private
     * @type {import('views/record/list-tree').default}
     */
    rootView

    /**
     * @type {boolean}
     */
    isUnfolded = false

    data() {
        return {
            name: this.model.attributes.name,
            isUnfolded: this.isUnfolded,
            showFold: this.isUnfolded && !this.isEnd,
            showUnfold: !this.isUnfolded && !this.isEnd,
            isEnd: this.isEnd,
            isSelected: this.isSelected,
            readOnly: this.readOnly,
            isMovable: this.options.moveSupported && !this.options.readOnly,
        };
    }

    /**
     * @param {{
     *     moveSupported: boolean,
     *     readOnly: boolean,
     *     isSelected?: boolean,
     *     level?: number,
     *     selectedData?: {path: string, name: Record},
     *     rootView: import('views/record/list-tree').default,
     *     selectable?: boolean,
     *     createDisabled?: boolean,
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setIsSelected() {
        this.isSelected = true;
        this.selectedData.id = this.model.id;

        const path = this.selectedData.path;
        const names = this.selectedData.names;

        path.length = 0;

        let view = this;

        while (1) {
            path.unshift(view.model.id);
            names[view.model.id] = view.model.attributes.name;

            if (view.getParentListView().level) {
                view = view.getParentView().getParentView();
            } else {
                break;
            }
        }
    }

    setup() {
        this.addActionHandler('unfold', e => {
            this.unfold();

            e.stopPropagation();
        });

        this.addActionHandler('fold', e => {
            this.fold()

            e.stopPropagation();
        });

        this.addActionHandler('remove', e => {
            this.actionRemove();

            e.stopPropagation();
        });

        if ('level' in this.options) {
            this.level = this.options.level;
        }

        if ('isSelected' in this.options) {
            this.isSelected = this.options.isSelected;
        }

        if ('selectedData' in this.options) {
            this.selectedData = this.options.selectedData;
        }

        this.readOnly = this.options.readOnly;

        if ('createDisabled' in this.options) {
            this.createDisabled = this.options.createDisabled;
        }

        if (this.readOnly) {
            this.createDisabled = true;
        }

        this.rootView = this.options.rootView;
        this.scope = this.model.entityType;

        this.isUnfolded = false;

        const childCollection = this.model.get('childCollection');

        if ((childCollection && childCollection.length === 0) || this.model.isEnd) {
            if (this.createDisabled) {
                this.isEnd = true;
            }
        }
        else if (childCollection) {
            childCollection.models.forEach(model => {
                if (~this.selectedData.path.indexOf(model.id)) {
                    this.isUnfolded = true;
                }
            });

            if (this.isUnfolded) {
                this.createChildren();
            }
        }

        this.on('select', o => {
            this.getParentListView().trigger('select', o);
        });
    }

    /**
     * @return {module:views/record/list-tree}
     */
    getParentListView() {
        return /** @type module:views/record/list-tree */this.getParentView();
    }

    /**
     * @private
     */
    createChildren() {
        const childCollection = this.model.get('childCollection');

        let callback = null;

        if (this.isRendered()) {
            callback = view => {
                this.listenToOnce(view, 'after:render', () => {
                    this.trigger('children-created');
                });

                view.render();
            };
        }

        this.createView('children', this.listViewName, {
            collection: childCollection,
            selector: '> .children',
            createDisabled: this.options.createDisabled,
            readOnly: this.options.readOnly,
            level: this.level + 1,
            selectedData: this.selectedData,
            model: this.model,
            selectable: this.options.selectable,
            rootView: this.rootView,
        }, callback);
    }

    /**
     * @private
     */
    checkLastChildren() {
        Espo.Ajax
            .getRequest(`${this.collection.entityType}/action/lastChildrenIdList`, {parentId: this.model.id})
            .then(idList => {
                const childrenView = this.getChildrenView();

                if (!childrenView) {
                    return;
                }

                idList.forEach(id => {
                    const model = this.model.get('childCollection').get(id);

                    if (model) {
                        model.isEnd = true;
                    }

                    /** @type {ListTreeRecordItemView|null} */
                    const itemView = childrenView.getView(id);

                    if (!itemView) {
                        return;
                    }

                    itemView.isEnd = true;

                    itemView.afterIsEnd();
                });

                // @todo Refactor.
                this.model.lastAreChecked = true;
            });
    }

    /**
     * Unfold.
     */
    async unfold() {
        if (this.createDisabled) {
            this.once('children-created', () => {
                if (!this.model.lastAreChecked) {
                    this.checkLastChildren();
                }
            });
        }

        const childCollection = this.model.get('childCollection');

        if (childCollection != null) {
            this.createChildren();
            this.isUnfolded = true;
            this.afterUnfold();

            this.trigger('after:unfold');

            return;
        }

        const collection = await this.getCollectionFactory().create(this.scope);

        collection.url = this.collection.url;
        collection.parentId = this.model.id;

        Espo.Ui.notifyWait();

        this.listenToOnce(collection, 'sync', () => {
            Espo.Ui.notify(false);

            this.model.set('childCollection', collection);

            this.createChildren();

            this.isUnfolded = true;

            if (collection.length || !this.createDisabled) {
                this.afterUnfold();

                this.trigger('after:unfold');
            } else {
                this.isEnd = true;

                this.afterIsEnd();
            }
        });

        await collection.fetch();
    }

    fold() {
        this.clearView('children');

        this.isUnfolded = false;

        this.afterFold();
    }

    afterRender() {
        if (this.isUnfolded) {
            this.afterUnfold();
        } else {
            this.afterFold();
        }

        if (this.isEnd) {
            this.afterIsEnd();
        }

        if (!this.readOnly) {
            const $remove = this.$el.find('> .cell [data-action="remove"]');

            this.$el.find('> .cell').on('mouseenter', () => {
                if (this.rootView.movedId) {
                    return;
                }
                $remove.removeClass('hidden');
            });

            this.$el.find('> .cell').on('mouseleave', () => {
                $remove.addClass('hidden');
            });
        }
    }

    /**
     * @private
     */
    afterFold() {
        this.$el.find('a[data-action="fold"][data-id="'+this.model.id+'"]').addClass('hidden');
        this.$el.find('a[data-action="unfold"][data-id="'+this.model.id+'"]').removeClass('hidden');
        this.$el.find(' > .children').addClass('hidden');
    }

    /**
     * @private
     */
    afterUnfold() {
        this.$el.find('a[data-action="unfold"][data-id="'+this.model.id+'"]').addClass('hidden');
        this.$el.find('a[data-action="fold"][data-id="'+this.model.id+'"]').removeClass('hidden');
        this.$el.find(' > .children').removeClass('hidden');
    }

    afterIsEnd() {
        this.$el.find('a[data-action="unfold"][data-id="'+this.model.id+'"]').addClass('hidden');
        this.$el.find('a[data-action="fold"][data-id="'+this.model.id+'"]').addClass('hidden');
        this.$el.find('span[data-name="white-space"][data-id="'+this.model.id+'"]').removeClass('hidden');
        this.$el.find(' > .children').addClass('hidden');
    }

    /*getCurrentPath() {
        let pointer = this;
        const path = [];

        while (true) {
            path.unshift(pointer.model.id);

            if (pointer.getParentView() === this.rootView) {
                break;
            }

            pointer = pointer.getParentView().getParentView();
        }

        return path;
    }*/

    /**
     * @private
     */
    async actionRemove() {
        await this.confirm({
            message: this.translate('removeRecordConfirmation', 'messages', this.scope),
            confirmText: this.translate('Remove'),
        });

        await this.model.destroy({wait: true});

        this.remove();
    }

    /**
     * @return {module:views/record/list-tree}
     */
    getChildrenView() {
        return /** @type module:views/record/list-tree */this.getView('children');
    }
}

export default ListTreeRecordItemView;

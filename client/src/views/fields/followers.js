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

import LinkMultipleFieldView from 'views/fields/link-multiple';

class FollowersFieldView extends LinkMultipleFieldView {

    foreignScope = 'User'
    portionSize = 6
    linkClass = 'text-default'

    setup() {
        super.setup();

        this.addActionHandler('showMoreFollowers', (e, target) => {
            this.showMoreFollowers();

            $(target).remove();
        });

        this.portionSize = this.getConfig().get('recordFollowersLoadLimit') || this.portionSize;

        this.limit = this.portionSize;

        this.listenTo(this.model, 'change:isFollowed', () => {
            const idList = this.model.get(this.idsName) || [];

            if (this.model.get('isFollowed')) {
                if (!idList.includes(this.getUser().id)) {
                    idList.unshift(this.getUser().id);

                    const nameMap = this.model.get(this.nameHashName) || {};

                    nameMap[this.getUser().id] = this.getUser().get('name');

                    this.model.trigger('change:' + this.idsName);

                    this.reRender();
                }

                return;
            }

            const index = idList.indexOf(this.getUser().id);

            if (~index) {
                idList.splice(index, 1);

                this.model.trigger('change:' + this.idsName);

                this.reRender();
            }
        });
    }

    /*reloadFollowers() {
        this.getCollectionFactory().create('User', collection => {
            collection.url = this.model.entityType + '/' + this.model.id + '/followers';
            collection.offset = 0;
            collection.maxSize = this.limit;

            this.listenToOnce(collection, 'sync', () => {
                let idList = [];
                let nameMap = {};

                collection.forEach(user => {
                    idList.push(user.id);
                    nameMap[user.id] = user.get('name');
                });

                this.model.set(this.idsName, idList);
                this.model.set(this.nameHashName, nameMap);

                this.reRender();
            });

            collection.fetch();
        });
    }*/

    showMoreFollowers() {
        this.getCollectionFactory().create('User', collection => {
            collection.url = this.model.entityType + '/' + this.model.id + '/followers';
            collection.offset = this.ids.length || 0;
            collection.maxSize = this.portionSize;
            collection.data.select = ['id', 'name'].join(',');
            collection.orderBy = null;
            collection.order = null;

            this.listenToOnce(collection, 'sync', () => {
                const idList = this.model.get(this.idsName) || [];
                const nameMap = this.model.get(this.nameHashName) || {};

                collection.forEach(user => {
                    idList.push(user.id);
                    nameMap[user.id] = user.get('name');
                });

                this.limit += this.portionSize;

                this.model.trigger('change:' + this.idsName);

                this.reRender();
            });

            collection.fetch();
        });
    }

    getValueForDisplay() {
        if (this.mode === this.MODE_DETAIL || this.mode === this.MODE_LIST) {
            const list = [];

            this.ids.forEach(id => {
                list.push(this.getDetailLinkHtml(id));
            });

            let str = null;

            if (list.length) {
                str = '' + list.join(', ') + '';
            }

            if (list.length >= this.limit) {
                str += ', <a role="button" data-action="showMoreFollowers">...</a>';
            }

            return str;
        }
    }
}

export default FollowersFieldView;

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

import EnumFieldView from 'views/fields/enum';

export default class extends EnumFieldView {

    setup() {
        super.setup();

        this.translatedOptions = {'': `--${this.translate('All')}--`};

        this.params.options = [''];

        if (this.model.get('targetUserPosition') && this.model.get('teamId')) {
            this.params.options.push(this.model.get('targetUserPosition'));
        }

        this.loadRoleList(() => {
            if (this.mode === this.MODE_EDIT) {
                if (this.isRendered()) {
                    this.render();
                }
            }
        });

        this.listenTo(this.model, 'change:teamId', () => {
            this.loadRoleList(() => this.render());
        });
    }

    /**
     * @private
     * @param {function} callback
     */
    loadRoleList(callback) {
        const teamId = this.model.attributes.teamId;

        if (!teamId) {
            this.params.options = [''];
        }

        this.getModelFactory().create('Team', /** import('model').default */team => {
            team.id = teamId;

            team.fetch().then(() => {
                this.params.options = team.get('positionList') || [];
                this.params.options.unshift('');

                callback.call(this);
            });
        });
    }
}

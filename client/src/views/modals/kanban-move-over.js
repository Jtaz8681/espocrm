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

import ModalView from 'views/modal';
import OptionsProvider from 'helpers/field/options-provider';
import PipelinesHelper from 'helpers/misc/pipelines';

class KanbanMoveOverModalView extends ModalView {

    template = 'modals/kanban-move-over'

    /** @inheritDoc */
    backdrop = true

    data() {
        return {
            optionDataList: this.optionDataList,
        };
    }

    events = {
        /** @this KanbanMoveOverModalView */
        'click [data-action="move"]': function (e) {
            const value = $(e.currentTarget).data('value');

            this.moveTo(value);
        },
    }

    setup() {
        this.scope = this.model.entityType;

        const iconHtml = this.getHelper().getScopeColorIconHtml(this.scope);

        this.statusField = this.options.statusField;

        this.$header = $('<span>');

        this.$header.append(
            $('<span>').text(this.getLanguage().translate(this.scope, 'scopeNames'))
        );

        if (this.model.get('name')) {
            this.$header.append(' <span class="chevron-right"></span> ');
            this.$header.append(
                $('<span>').text(this.model.get('name'))
            )
        }

        this.$header.prepend(iconHtml);

        this.buttonList = [
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];
        this.setupGroupDataList();
    }

    /**
     * @private
     */
    setupGroupDataList() {
        if (this.options.pipelineId) {
            const pipeline = new PipelinesHelper().get(this.scope)
                .find(it => it.id === this.options.pipelineId);

            const stages = pipeline?.stages


            this.optionDataList = stages.map(it => {
                return {
                    value: it.id,
                    label: it.name,
                };
            });

            return;
        }

        const options = new OptionsProvider().get(this.scope, this.statusField)
            .filter(it => it.name);

        this.optionDataList = options.map(it => {
            return {
                value: it.name,
                label: it.label,
            };
        });
    }

    /**
     * @private
     * @param {string} status
     */
    moveTo(status) {
        const previousStatus = this.model.attributes[this.statusField];

        this.model
            .save({[this.statusField]: status}, {
                patch: true,
                isMoveTo: true,
            })
            .then(() => {
                Espo.Ui.success(this.translate('Done'));
            })
            .catch(() => {
                this.model.setMultiple({[this.statusField]: previousStatus}, {
                    isMoveTo: true,
                });
            });

        this.close();
    }
}

// noinspection JSUnusedGlobalSymbols
export default KanbanMoveOverModalView;

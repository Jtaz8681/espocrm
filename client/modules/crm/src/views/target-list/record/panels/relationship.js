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

define('crm:views/target-list/record/panels/relationship', ['views/record/panels/relationship'], function (Dep) {

    return Dep.extend({

        fetchOnModelAfterRelate: true,

        actionOptOut: function (data) {
            this.confirm(this.translate('confirmation', 'messages'), () => {
                Espo.Ajax
                    .postRequest('TargetList/action/optOut', {
                        id: this.model.id,
                        targetId: data.id,
                        targetType: data.type,
                    })
                    .then(() => {
                        this.collection.fetch();
                        Espo.Ui.success(this.translate('Done'));
                        this.model.trigger('opt-out');
                    });
            });
        },

        actionCancelOptOut: function (data) {
            this.confirm(this.translate('confirmation', 'messages'), () => {
                Espo.Ajax
                    .postRequest('TargetList/action/cancelOptOut', {
                        id: this.model.id,
                        targetId: data.id,
                        targetType: data.type,
                    })
                    .then(() => {
                        this.collection.fetch();
                        Espo.Ui.success(this.translate('Done'));

                        this.collection.fetch();
                        this.model.trigger('cancel-opt-out');
                    });
            });
        },
    });
});
